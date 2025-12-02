<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ScheduledTask;
use App\Services\ScheduledTaskService;
use App\Jobs\ExecuteScheduledTask;

class ScheduledTaskController extends Controller
{
    // 
    protected $service;


    public function __construct(ScheduledTaskService $service)
    {
        $this->service = $service;
        // $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ScheduledTask::query();


        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }


        if ($request->has('executor_type')) {
            $query->where('executor_type', $request->query('executor_type'));
        }


        $tasks = $query->orderBy('schedule_date', 'desc')->paginate(20);


        return view('tarefas.dashboard', [
            'today'        => ScheduledTask::whereDate('schedule_date', today())->count(),
            'future'       => ScheduledTask::where('schedule_date', '>', now())->count(),
            'overdue'      => ScheduledTask::where('schedule_date', '<', now())
                                            ->where('status', 'pending')
                                            ->count(),
            'aiPending'    => ScheduledTask::where('executor_type', 'ai')
                                           ->where('approved', false)
                                           ->count(),
            'aiSuggestions' => ScheduledTask::where('executor_type', 'ai')
                                            ->where('approved', false)
                                            ->orderBy('schedule_date')
                                            ->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'schedule_date' => 'required|date',
            'recurrence' => 'nullable|string',
            'payload' => 'nullable|array',
        ]);


        $data['created_by'] = Auth::id();
        $task = $this->service->createTask($data);


        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = ScheduledTask::with(['executions', 'notifications', 'auditItems'])->findOrFail($id);
        return response()->json($task);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = ScheduledTask::findOrFail($id);
        $this->authorize('update', $task);


        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'schedule_date' => 'sometimes|date',
            'recurrence' => 'nullable|string',
            'payload' => 'nullable|array',
            'approved' => 'nullable|boolean',
        ]);


        $task = $this->service->updateTask($task, $data);


        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // POST /api/scheduled-tasks/{id}/approve
    public function approve(Request $request, $id)
    {
        $task = ScheduledTask::findOrFail($id);
        $this->authorize('approve', $task);

        $task = $this->service->approveTask($task, Auth::id());

        return response()->json($task);
    }


    // POST /api/scheduled-tasks/{id}/run-now
    public function runNow(Request $request, $id)
    {
        $task = ScheduledTask::findOrFail($id);
        $this->authorize('run', $task);


        // dispatch job immediately
        // Dispatch imediato para a queue 'tasks' (Laravel Queue System)
        // Esta chamada envia o Job para processamento assíncrono
        // Certifique-se que o worker está ativo: php artisan queue:work --queue=tasks
        ExecuteScheduledTask::dispatch($task->id)->onQueue('tasks');

        return response()->json(['message' => 'Task queued for immediate execution']);
    }
}
