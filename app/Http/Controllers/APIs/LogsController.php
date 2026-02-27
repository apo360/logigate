<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LogsController
{
    public function getLogAlerts()
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return response()->json(['message' => 'Log file not found'], 404);
        }

        $logs = file_get_contents($logPath);
        $filteredContent = $this->filterLogContent($logs); // Método para filtrar logs, se necessário

        return response()->json(['logs' => $filteredContent]);
    }

    private function filterLogContent($logs)
    {
        // Exemplo de filtro simples para os logs
        $lines = explode(PHP_EOL, $logs);
        $filtered = array_filter($lines, function ($line) {
            return !empty($line); // Remove linhas vazias
        });

        return array_values($filtered);
    }
}

