<?php

namespace App\Core;

/**
 * Clase para manejar la carga de archivos
 */
class FileUpload
{
    private array $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    private array $allowedVideoTypes = ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo'];
    private int $maxFileSize = 10 * 1024 * 1024; // 10MB
    private string $uploadDir;

    public function __construct()
    {
        $this->uploadDir = ROOT_PATH . '/public/uploads/';
        
        // Crear directorio si no existe
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Sube un archivo y retorna el nombre del archivo guardado
     */
    public function upload(array $file): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validar tipo de archivo
        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, array_merge($this->allowedImageTypes, $this->allowedVideoTypes))) {
            return null;
        }

        // Validar tamaño
        if ($file['size'] > $this->maxFileSize) {
            return null;
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('ticket_', true) . '.' . $extension;
        $filePath = $this->uploadDir . $fileName;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $fileName;
        }

        return null;
    }

    /**
     * Obtiene la URL pública del archivo
     */
    public function getPublicUrl(string $fileName): string
    {
        return '/proyectoWebTicket/public/uploads/' . $fileName;
    }

    /**
     * Verifica si un archivo es una imagen
     */
    public function isImage(string $fileName): bool
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array($extension, $imageExtensions);
    }

    /**
     * Verifica si un archivo es un video
     */
    public function isVideo(string $fileName): bool
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $videoExtensions = ['mp4', 'mpeg', 'mov', 'avi'];
        return in_array($extension, $videoExtensions);
    }
}
