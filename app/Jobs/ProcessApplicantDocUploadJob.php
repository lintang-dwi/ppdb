<?php

namespace App\Jobs;

use App\Models\PpdbDocument;
use App\Services\DocumentCompressionService;
use App\Services\GoogleDriveClientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessApplicantDocUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $documentId,
        public string $localTempPath,
        public string $schoolCode,
        public string $year,
        public string $regNumber,
        public string $applicantName
    ) {
        $this->onQueue('documents');
    }

    public function handle(DocumentCompressionService $compression, GoogleDriveClientService $drive): void
    {
        $document = PpdbDocument::withoutGlobalScopes()->find($this->documentId);
        if (! $document) {
            Log::warning("PpdbDocument [{$this->documentId}] not found. Skipping upload.");

            return;
        }

        $fullLocalPath = Storage::disk('local')->path($this->localTempPath);
        if (! file_exists($fullLocalPath)) {
            Log::error("Temp file not found for doc [{$this->documentId}]: {$fullLocalPath}");

            return;
        }

        // 1. Layer 2 server compression safety net
        $compression->compressIfNeeded($fullLocalPath);

        // 2. Ensure 3-tier folder in Google Drive
        $folderId = $drive->ensureApplicantFolder(
            $this->schoolCode,
            $this->year,
            $this->regNumber,
            $this->applicantName
        );

        // 3. Upload file to Google Drive
        $mime = mime_content_type($fullLocalPath) ?: 'application/octet-stream';
        $uploadResult = $drive->uploadFile(
            $fullLocalPath,
            $document->original_file_name ?: basename($this->localTempPath),
            $mime,
            $folderId,
            $this->schoolCode
        );

        // 4. Update document record
        $document->update([
            'drive_file_id' => $uploadResult['drive_file_id'],
            'drive_view_url' => $uploadResult['drive_view_url'],
        ]);

        // 5. Clean up temporary local file after successful upload
        Storage::disk('local')->delete($this->localTempPath);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error("Final failure uploading doc [{$this->documentId}] to Drive: {$exception?->getMessage()}", [
            'document_id' => $this->documentId,
            'local_path' => $this->localTempPath,
            'school_code' => $this->schoolCode,
            'reg_number' => $this->regNumber,
        ]);
        // Keep the temporary local file on final failure for manual recovery
    }
}
