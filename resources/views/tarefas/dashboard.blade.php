<!-- resources/views/leander/dashboard.blade.php -->
<x-app-layout>
    <div class="p-6 space-y-8">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold">Agenda Inteligente de Tarefas — LEANDER</h1>
            <button onclick="Livewire.dispatch('openCreateTask')" class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700">Criar Tarefa</button>
        </div>

        <!-- Cards de Resumo -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="p-5 bg-white shadow rounded-xl">
                <p class="text-gray-600">Tarefas de Hoje</p>
                <p class="text-4xl font-bold text-blue-600">{{ $today }}</p>
            </div>
            <div class="p-5 bg-white shadow rounded-xl">
                <p class="text-gray-600">Futuras</p>
                <p class="text-4xl font-bold text-green-600">{{ $future }}</p>
            </div>
            <div class="p-5 bg-white shadow rounded-xl">
                <p class="text-gray-600">Atrasadas</p>
                <p class="text-4xl font-bold text-red-600">{{ $overdue }}</p>
            </div>
            <div class="p-5 bg-white shadow rounded-xl">
                <p class="text-gray-600">IA — A Aprovar</p>
                <p class="text-4xl font-bold text-purple-600">{{ $aiPending }}</p>
            </div>
        </div>

        <!-- Calendário -->
        <div class="bg-white p-6 shadow rounded-xl">
            <h2 class="text-xl font-semibold mb-4">Calendário</h2>
            @livewire('calendar-task-view')
        </div>

        <!-- Sugestões da IA -->
        <div class="bg-white p-6 shadow rounded-xl space-y-4">
            <h2 class="text-xl font-semibold flex items-center gap-2">🧠 Sugestões Inteligentes da IA</h2>

            @forelse ($aiSuggestions as $task)
                <div class="flex items-center justify-between border rounded-lg p-3 bg-gray-50">
                    <div class="max-w-xl">
                        <p class="font-semibold">{{ $task->title }}</p>
                        <p class="text-sm text-gray-600">{{ $task->description }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="approve({{ $task->id }})" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Aprovar</button>
                        <button wire:click="reject({{ $task->id }})" class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Rejeitar</button>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nenhuma recomendação da IA neste momento.</p>
            @endforelse
        </div>

        <!-- Modal Criar Tarefa -->
        @livewire('task-create-modal')

    </div>
</x-app-layout>
