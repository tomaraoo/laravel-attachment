<?php

namespace Rft\LaravelAttachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rft\LaravelAttachment\Models\FileAttachment;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AttachmentManager
{
    /**
     * Get the configured filesystem disk name (defaults to 'private' -> storage/app/private).
     */
    public function diskName(): string
    {
        return config('laravel-attachment.disk', 'private');
    }

    /**
     * Generate a unique hashed file_url token.
     */
    public function generateHashedUrl(string $originalName, string $modelClass): string
    {
        do {
            $hash = hash('sha256', Str::uuid()->toString() . '|' . $modelClass . '|' . $originalName . '|' . microtime(true) . '|' . random_bytes(16));
        } while (FileAttachment::query()->where('file_url', $hash)->exists());

        return $hash;
    }

    /**
     * Store a single UploadedFile transactionally into storage/app/private and insert into file-attachement.
     */
    public function store(
        UploadedFile $file,
        Model|string $model,
        int|string|null $modelId = null,
        ?int $userId = null
    ): FileAttachment {
        [$modelClass, $resolvedModelId] = $this->resolveModelAndId($model, $modelId);
        $resolvedUserId = $userId ?? Auth::id();
        $originalName = $file->getClientOriginalName();
        $hashedUrl = $this->generateHashedUrl($originalName, $modelClass);
        $disk = $this->diskName();

        return DB::transaction(function () use ($file, $modelClass, $resolvedModelId, $resolvedUserId, $originalName, $hashedUrl, $disk) {
            $storedPath = Storage::disk($disk)->putFileAs('', $file, $hashedUrl);

            if (! $storedPath) {
                throw new RuntimeException("Failed to store attachment file [{$originalName}] in private storage.");
            }

            try {
                return FileAttachment::create([
                    'model' => $modelClass,
                    'model_id' => $resolvedModelId,
                    'file_name' => $originalName,
                    'file_url' => $hashedUrl,
                    'user_id' => $resolvedUserId,
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                Storage::disk($disk)->delete($hashedUrl);
                throw $e;
            }
        });
    }

    /**
     * Store multiple UploadedFile instances transactionally.
     *
     * @param  iterable<UploadedFile>  $files
     * @return Collection<int, FileAttachment>
     */
    public function storeMany(
        iterable $files,
        Model|string $model,
        int|string|null $modelId = null,
        ?int $userId = null
    ): Collection {
        $storedHashes = [];
        $disk = $this->diskName();

        try {
            return DB::transaction(function () use ($files, $model, $modelId, $userId, &$storedHashes) {
                $attachments = collect();

                foreach ($files as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        $attachment = $this->store($file, $model, $modelId, $userId);
                        $storedHashes[] = $attachment->file_url;
                        $attachments->push($attachment);
                    }
                }

                return $attachments;
            });
        } catch (Throwable $e) {
            foreach ($storedHashes as $hash) {
                Storage::disk($disk)->delete($hash);
            }
            throw $e;
        }
    }

    /**
     * Store uploaded files directly from an HTTP Request for a given model.
     *
     * @return Collection<int, FileAttachment>
     */
    public function storeFromRequest(
        Request $request,
        Model|string $model,
        int|string|null $modelId = null,
        string $inputName = 'attachments',
        ?int $userId = null
    ): Collection {
        if (! $request->hasFile($inputName)) {
            return collect();
        }

        $files = $request->file($inputName);
        if (! is_array($files)) {
            $files = [$files];
        }

        return $this->storeMany($files, $model, $modelId, $userId);
    }

    /**
     * Find an attachment by its hashed file_url.
     */
    public function findByUrl(string $fileUrl): ?FileAttachment
    {
        return FileAttachment::query()->where('file_url', $fileUrl)->first();
    }

    /**
     * Find an attachment by its hashed file_url or fail with 404.
     */
    public function findByUrlOrFail(string $fileUrl): FileAttachment
    {
        return FileAttachment::query()->where('file_url', $fileUrl)->firstOrFail();
    }

    /**
     * Retrieve all attachments for a given model class (and optional model ID).
     *
     * @return Collection<int, FileAttachment>
     */
    public function forModel(Model|string $model, int|string|null $modelId = null): Collection
    {
        [$modelClass, $resolvedModelId] = $this->resolveModelAndId($model, $modelId);

        $query = FileAttachment::query()
            ->where('model', $modelClass)
            ->orderByDesc('id');

        if ($resolvedModelId !== null) {
            $query->where('model_id', $resolvedModelId);
        }

        return $query->get();
    }

    /**
     * Stream (view inline) or download an attachment via its hashed file_url.
     */
    public function response(string $fileUrl, bool $download = false): StreamedResponse
    {
        $attachment = $this->findByUrlOrFail($fileUrl);
        $disk = $this->diskName();
        $storagePath = $attachment->getStoragePath();

        abort_unless(
            Storage::disk($disk)->exists($storagePath),
            404,
            'Attachment file not found in private storage.'
        );

        $mimeType = Storage::disk($disk)->mimeType($storagePath) ?: 'application/octet-stream';
        $headers = [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($download) {
            return Storage::disk($disk)->download($storagePath, $attachment->file_name, $headers);
        }

        return Storage::disk($disk)->response($storagePath, $attachment->file_name, $headers);
    }

    /**
     * Delete an attachment transactionally by its hashed file_url.
     */
    public function deleteByUrl(string $fileUrl): bool
    {
        $attachment = $this->findByUrlOrFail($fileUrl);

        return DB::transaction(function () use ($attachment) {
            return (bool) $attachment->delete();
        });
    }

    /**
     * Delete all attachments belonging to a specific model record transactionally.
     */
    public function deleteForModel(Model|string $model, int|string|null $modelId = null): int
    {
        $attachments = $this->forModel($model, $modelId);

        return DB::transaction(function () use ($attachments) {
            $deletedCount = 0;

            foreach ($attachments as $attachment) {
                if ($attachment->delete()) {
                    $deletedCount++;
                }
            }

            return $deletedCount;
        });
    }

    /**
     * Resolve model class name and primary key ID from a Model instance or class string.
     *
     * @return array{0: string, 1: int|string|null}
     */
    public function resolveModelAndId(Model|string $model, int|string|null $modelId = null): array
    {
        if ($model instanceof Model) {
            return [get_class($model), $modelId ?? $model->getKey()];
        }

        return [ltrim($model, '\\'), $modelId];
    }
}
