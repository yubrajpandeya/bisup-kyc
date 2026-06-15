<?php

namespace Bisup\PtrPort25;

use InvalidArgumentException;
use RuntimeException;
use WHMCS\Config\Setting;
use WHMCS\Database\Capsule;

class KycManager
{
    public static function storeUploadedDocument(int $requestId, int $clientId, array $file, string $documentType, array $moduleSettings): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('KYC upload failed.');
        }

        $allowed = self::allowedExtensions($moduleSettings['allowed_file_types'] ?? 'pdf,jpg,jpeg,png');
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported KYC file type.');
        }

        $maxBytes = max(1, (int) ($moduleSettings['max_kyc_file_size_mb'] ?? 5)) * 1024 * 1024;
        if ((int) $file['size'] > $maxBytes) {
            throw new InvalidArgumentException('KYC file is larger than the allowed limit.');
        }

        $mimeType = self::detectMimeType($file['tmp_name']);
        if (!self::isAllowedMime($mimeType, $extension, $file['tmp_name'])) {
            throw new InvalidArgumentException('KYC file MIME type is not allowed.');
        }

        $storageDir = self::storageDirectory();
        if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true) && !is_dir($storageDir)) {
            throw new RuntimeException('Unable to create KYC storage directory.');
        }

        $storedName = sprintf(
            '%d_%d_%s.%s',
            $clientId,
            $requestId,
            bin2hex(random_bytes(12)),
            $extension
        );
        $targetPath = $storageDir . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Unable to store KYC document.');
        }

        Capsule::table('mod_bisup_ptr25_kyc_documents')->insert([
            'request_id' => $requestId,
            'client_id' => $clientId,
            'document_type' => $documentType,
            'original_filename' => $file['name'],
            'stored_filename' => $storedName,
            'file_path' => $targetPath,
            'mime_type' => $mimeType,
            'file_size' => (int) $file['size'],
            'verification_status' => 'pending',
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLogger::log([
            'request_id' => $requestId,
            'client_id' => $clientId,
            'action' => 'kyc_uploaded',
            'note' => 'KYC document uploaded: ' . $documentType,
        ]);
    }

    public static function storageDirectory(): string
    {
        $attachmentsDir = Setting::getValue('Attachments Directory') ?: ROOTDIR . DIRECTORY_SEPARATOR . 'attachments';
        return rtrim($attachmentsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'bisup_ptr_port25';
    }

    private static function allowedExtensions(string $value): array
    {
        return array_values(array_filter(array_map(static function ($item) {
            return strtolower(trim($item));
        }, explode(',', $value))));
    }

    private static function detectMimeType(string $path): string
    {
        if (\function_exists('finfo_open')) {
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            $mime = \finfo_file($finfo, $path);
            \finfo_close($finfo);
            return $mime ?: 'application/octet-stream';
        }

        if (\function_exists('mime_content_type')) {
            return \mime_content_type($path) ?: 'application/octet-stream';
        }

        return 'application/octet-stream';
    }

    private static function isAllowedMime(string $mimeType, string $extension, string $path): bool
    {
        $allowed = [
            'pdf' => ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf'],
            'jpg' => ['image/jpeg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/pjpeg'],
            'png' => ['image/png', 'image/x-png'],
        ];

        if (in_array($mimeType, $allowed[$extension] ?? [], true)) {
            return self::hasValidFileSignature($extension, $path);
        }

        $genericMimeTypes = [
            'application/octet-stream',
            'binary/octet-stream',
            'application/download',
            'application/force-download',
            'unknown/unknown',
        ];

        return in_array($mimeType, $genericMimeTypes, true)
            && self::hasValidFileSignature($extension, $path);
    }

    private static function hasValidFileSignature(string $extension, string $path): bool
    {
        $handle = @\fopen($path, 'rb');
        if (!$handle) {
            return false;
        }

        $bytes = \fread($handle, 16) ?: '';
        \fclose($handle);

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            return \str_starts_with($bytes, "\xFF\xD8\xFF");
        }

        if ($extension === 'png') {
            return \str_starts_with($bytes, "\x89PNG\r\n\x1A\n");
        }

        if ($extension === 'pdf') {
            return \str_starts_with($bytes, '%PDF-');
        }

        return false;
    }
}
