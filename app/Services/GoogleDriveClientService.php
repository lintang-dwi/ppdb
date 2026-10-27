<?php

namespace App\Services;

use App\Models\School;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;

class GoogleDriveClientService
{
    /** @var array<string, Drive> */
    protected array $drives = [];

    public function __construct(
        protected ?Drive $mockDrive = null
    ) {}

    public function getDriveServiceForSchool(string $schoolCode): Drive
    {
        if ($this->mockDrive) {
            return $this->mockDrive;
        }

        if (isset($this->drives[$schoolCode])) {
            return $this->drives[$schoolCode];
        }

        $school = School::where('school_code', $schoolCode)->first();
        $refreshToken = $school?->getGoogleDriveRefreshTokenDecrypted()
            ?? config('services.google_drive.refresh_token');

        if (! $refreshToken) {
            throw new RuntimeException("Sekolah [{$schoolCode}] belum menghubungkan akun Google Drive.");
        }

        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');

        if (! $clientId || ! $clientSecret) {
            throw new RuntimeException('Google Drive Client ID / Secret belum dikonfigurasi di server.');
        }

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->refreshToken($refreshToken);

        $drive = new Drive($client);
        $this->drives[$schoolCode] = $drive;

        return $drive;
    }

    public function getOrCreateFolder(string $name, ?string $parentId, Drive $drive): string
    {
        $query = "mimeType = 'application/vnd.google-apps.folder' and name = '{$name}' and trashed = false";
        if ($parentId && $parentId !== 'root') {
            $query .= " and '{$parentId}' in parents";
        }

        $results = $drive->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        ]);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        $metadata = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => ($parentId && $parentId !== 'root') ? [$parentId] : [],
        ]);

        $folder = $drive->files->create($metadata, ['fields' => 'id']);

        return $folder->getId();
    }

    public function ensureApplicantFolder(string $schoolCode, string $year, string $regNumber, string $applicantName): string
    {
        $drive = $this->getDriveServiceForSchool($schoolCode);
        $school = School::where('school_code', $schoolCode)->first();
        $rootParentId = $school?->google_drive_root_folder_id ?: config('services.google_drive.root_folder_id', 'root');

        // 1. Year batch folder in school's drive (e.g. PPDB_2026)
        $yearFolderId = $this->getOrCreateFolder("PPDB_{$year}", $rootParentId, $drive);

        // 2. Applicant folder (e.g. REG-SMAN1-2026-0001_FAUZAN_HAKIM)
        $cleanName = strtoupper(trim(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $applicantName)));
        $cleanReg = trim(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $regNumber));
        $applicantFolderName = "{$cleanReg}_{$cleanName}";

        return $this->getOrCreateFolder($applicantFolderName, $yearFolderId, $drive);
    }

    public function uploadFile(string $fullPath, string $fileName, ?string $mimeType, string $folderId, ?string $schoolCode = null): array
    {
        $drive = $schoolCode
            ? $this->getDriveServiceForSchool($schoolCode)
            : ($this->mockDrive ?? ($this->drives[array_key_first($this->drives)] ?? null));

        if (! $drive) {
            throw new RuntimeException('Google Drive client instance is not available for upload.');
        }

        $mime = $mimeType ?: (mime_content_type($fullPath) ?: 'application/octet-stream');
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
        ]);

        $file = $drive->files->create($fileMetadata, [
            'data' => file_get_contents($fullPath),
            'mimeType' => $mime,
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink',
        ]);

        $fileId = $file->getId();
        $viewUrl = $file->getWebViewLink() ?: "https://drive.google.com/file/d/{$fileId}/view";

        return [
            'drive_file_id' => $fileId,
            'drive_view_url' => $viewUrl,
        ];
    }
}
