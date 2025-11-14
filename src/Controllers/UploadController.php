<?php
namespace Src\Controllers;

use Src\Helpers\Response;

class UploadController extends BaseController
{
    public function store()
    {
        // Pastikan tipe konten bukan JSON (karena upload butuh multipart/form-data)
        if (($_SERVER['CONTENT_TYPE'] ?? '') && 
            str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            return $this->error(415, 'Use multipart/form-data for upload');
        }

        // Pastikan ada file dikirim
        if (empty($_FILES['file'])) {
            return $this->error(422, 'file is required');
        }

        $f = $_FILES['file'];

        // Cek error upload
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return $this->error(400, 'Upload error');
        }

        // Maksimum ukuran file 2MB
        if ($f['size'] > 2 * 1024 * 1024) {
            return $this->error(422, 'Max 2MB');
        }

        // Cek mime type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);

        $allowed = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'application/pdf' => 'pdf'
        ];

        if (!isset($allowed[$mime])) {
            return $this->error(422, 'Invalid mime');
        }

        // Buat nama unik untuk file
        $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];

        // Pastikan folder uploads ada
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Tentukan lokasi file disimpan
        $dest = $uploadDir . $name;

        // Pindahkan file dari tmp ke folder uploads
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            return $this->error(500, 'Save failed');
        }

        // Sukses
        return $this->ok([
            'message' => 'File uploaded successfully',
            'path' => "/uploads/$name"
        ], 201);
    }
}