<?php

namespace App\Http\Controllers;
use illuminate\Http\Request;
use App\Services\SAFtParser;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Storage;

class SAFtController extends Controller
{
    protected $safTParser;
    protected $openAIService;

    public function __construct(SAFtParser $safTParser, OpenAIService $openAIService)
    {
        $this->safTParser = $safTParser;
        $this->openAIService = $openAIService;
    }

    public function index()
    {
        return view('safT.upload');
    }

    public function parseSAFT(Request $request)
    {
        $file = $request->file('safTFile');
        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'Invalid file'], 400);
        }

        // Store the file temporarily
        $path = $file->store('saft_files');
        if (!$path) {
            return response()->json(['error' => 'Failed to store file'], 500);
        }
        // Ensure the file is a valid SAFT file (you can add more validation here)
        if ($file->getClientOriginalExtension() !== 'xml') {
            Storage::delete($path); // Clean up the temporary file
            return response()->json(['error' => 'Invalid file type. Only XML files are allowed.'], 400);
        }
        // Ensure the file size is within limits (optional)
        if ($file->getSize() > 5000000) { // 5MB limit
            Storage::delete($path); // Clean up the temporary file
            return response()->json(['error' => 'File size exceeds the limit of 5MB.'], 400);
        }

        // Parse the SAFT file
        try {
            $data = $this->safTParser->parse(storage_path("app/{$path}"));
            Storage::delete($path); // Clean up the temporary file
            return response()->json($data);
        } catch (\Exception $e) {
            Storage::delete($path); // Clean up on error
            return response()->json(['error' => 'Failed to parse SAFT file: ' . $e->getMessage()], 500);
        }

        // Open AI Service Interaction (optional)
        $openAIService = new OpenAIService();
        $response = $openAIService->analyze('Process the SAFT data: ' . json_encode($data));
        
        // Return the response from OpenAI
        if (!$response) {
            return response()->json(['error' => 'Failed to get response from OpenAI'], 500);
        }
        // Return the response from OpenAI
        return response()->json(['response' => $response]);
    }
    public function showForm()
    {
        return view('safT.form');
    }
    public function handleForm(Request $request)
    {
        // Handle the form submission logic here
        // For example, you can validate the input and save it to the database
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            // Add more validation rules as needed
        ]);

        // Process the validated data (e.g., save to database)
        // ...

        return redirect()->back()->with('success', 'Form submitted successfully!');
    }
    public function downloadSAFT(Request $request)
    {
        // Logic to download the SAFT file
        $filePath = storage_path('app/saft_files/sample.saft'); // Adjust the path as needed
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath, 'sample.saft');
    }
    public function uploadSAFT(Request $request)
    {
        // Logic to handle SAFT file upload
        $file = $request->file('safTFile');
        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'Invalid file'], 400);
        }

        // Store the file temporarily
        $path = $file->store('saft_files');
        if (!$path) {
            return response()->json(['error' => 'Failed to store file'], 500);
        }

        // Return success response
        return response()->json(['message' => 'File uploaded successfully', 'path' => $path]);
    }
    public function downloadFile($fileName)
    {
        // Logic to download a specific file
        $filePath = storage_path("app/saft_files/{$fileName}");
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath, $fileName);
    }
    public function deleteFile($fileName)
    {
        // Logic to delete a specific file
        $filePath = storage_path("app/saft_files/{$fileName}");
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        if (unlink($filePath)) {
            return response()->json(['message' => 'File deleted successfully']);
        } else {
            return response()->json(['error' => 'Failed to delete file'], 500);
        }
    }
    public function listFiles()
    {
        // Logic to list all files in the SAFT directory
        $files = Storage::files('saft_files');
        $fileList = array_map(function ($file) {
            return basename($file);
        }, $files);

        return response()->json(['files' => $fileList]);
    }
}