<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FileHelper
{
    /**
     * Faz o upload de um arquivo.
     *
     * @param string $fileKey
     * @param string $destinationPath
     * @param string|null $fileNamePrefix
     * @return string|false
     */
    public static function upload($fileKey, $destinationPath, $fileNamePrefix = null)
    {
        if (request()->hasFile($fileKey)) {
            $file = request()->file($fileKey);
            $extension = $file->getClientOriginalExtension();
            $fileName = ($fileNamePrefix ? $fileNamePrefix . '_' : '') . uniqid() . '.' . $extension;
            $file->storeAs($destinationPath, $fileName, 'private');
            return $fileName;
        }
        return false;
    }

    /**
     * Faz o download de um arquivo.
     *
     * @param string $filePath
     * @param string|null $fileName
     * @param array $headers
     * @return \Illuminate\Http\Response
     */
    public static function download($filePath, $fileName = null, $headers = [])
    {
        $defaultHeaders = [
            'Content-Type' => File::mimeType($filePath),
        ];
        $headers = array_merge($defaultHeaders, $headers);
        return response()->download($filePath, $fileName, $headers);
    }

    /**
     * Exclui um arquivo.
     *
     * @param string $filePath
     * @return bool
     */
    public static function delete($filePath)
    {
        if (Storage::exists($filePath)) {
            return Storage::delete($filePath);
        }
        return false;
    }

    /**
     * Renomeia um arquivo.
     *
     * @param string $filePath
     * @param string $newName
     * @return string|false
     */
    public static function rename($filePath, $newName)
    {
        if (Storage::exists($filePath)) {
            $directory = pathinfo($filePath, PATHINFO_DIRNAME);
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $newPath = $directory . '/' . $newName . '.' . $extension;
            if (Storage::move($filePath, $newPath)) {
                return $newPath;
            }
        }
        return false;
    }

    /**
     * Verifica se um arquivo existe.
     *
     * @param string $filePath
     * @return bool
     */
    public static function exists($filePath)
    {
        return Storage::exists($filePath);
    }

    /**
     * Obtém informações sobre um arquivo.
     *
     * @param string $filePath
     * @return array|false
     */
    public static function getInfo($filePath)
    {
        if (self::exists($filePath)) {
            return [
                'path' => $filePath,
                'size' => Storage::size($filePath),
                'last_modified' => Storage::lastModified($filePath),
            ];
        }
        return false;
    }

    /**
     * Cria um diretório.
     *
     * @param string $directoryPath
     * @return bool
     */
    public static function createDirectory($directoryPath)
    {
        if (!Storage::exists($directoryPath)) {
            return Storage::makeDirectory($directoryPath);
        }
        return false;
    }

    public static function saveFile($file, $clienteID, $processoID)
    {
        // Cria a pasta do cliente se não existir
        $clientePath = "clientes/{$clienteID}";
        self::createDirectory($clientePath);

        // Cria a subpasta do processo dentro da pasta do cliente se não existir
        $processoPath = "{$clientePath}/{$processoID}";
        self::createDirectory($processoPath);

        // Obtém o nome original do arquivo
        $nomeArquivo = $file->getClientOriginalName();

        // Salva o arquivo na pasta do processo
        $file->storeAs($processoPath, $nomeArquivo, 'public');

        // Retorna o caminho completo do arquivo no armazenamento seguro
        return "documentos/{$clientePath}/{$processoID}/{$nomeArquivo}";
    }

}
