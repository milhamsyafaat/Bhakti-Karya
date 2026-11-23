<?php

namespace App\Core;

/**
 * Penyimpanan berkas unggahan (produk, dll).
 */
class Storage
{
    private const ALLOWED = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    private const MAX_BYTES = 2097152; // 2 MB

    /**
     * Simpan file upload ke public/assets/uploads/<folder>.
     * @return string|null path relatif (untuk kolom gambar) atau null bila gagal.
     */
    public static function putUpload(array $file, string $folder = 'produk'): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > self::MAX_BYTES) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED[$ext])) {
            return null;
        }

        // Validasi MIME gambar (jika finfo tersedia)
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fi, $file['tmp_name']);
            finfo_close($fi);
            if ($mime && strpos($mime, 'image/') !== 0) {
                return null;
            }
        }

        $dir = BASE_PATH . '/public/assets/uploads/' . $folder;
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $name = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            return null;
        }

        return 'assets/uploads/' . $folder . '/' . $name;
    }
}
