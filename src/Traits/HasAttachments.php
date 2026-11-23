<?php

namespace Rft\LaravelAttachment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Rft\LaravelAttachment\Facades\Attachment;
use Rft\LaravelAttachment\Models\FileAttachment;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasAttachments
{
    /**
     * Get all file attachments associated with this model instance.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(FileAttachment::class, 'attachable', 'model', 'model_id')
            ->orderByDesc('id');
    }

    /**
     * Attach a single UploadedFile to this model instance transactionally.
     */
    public function attachFile(UploadedFile $file, ?int $userId = null): FileAttachment
    {
        return Attachment::store($file, $this, $this->getKey(), $userId);
    }

    /**
     * Attach multiple UploadedFile instances to this model instance transactionally.
     *
     * @param  iterable<UploadedFile>  $files
     * @return Collection<int, FileAttachment>
     */
    public function attachFiles(iterable $files, ?int $userId = null): Collection
    {
        return Attachment::storeMany($files, $this, $this->getKey(), $userId);
    }

    /**
     * Attach uploaded files from an HTTP request to this model instance.
     *
     * @return Collection<int, FileAttachment>
     */
    public function attachFromRequest(Request $request, string $inputName = 'attachments', ?int $userId = null): Collection
    {
        return Attachment::storeFromRequest($request, $this, $this->getKey(), $inputName, $userId);
    }

    /**
     * Find a specific attachment belonging to this model instance by its hashed file_url.
     */
    public function findAttachment(string $fileUrl): ?FileAttachment
    {
        return Attachment::findForModel($this, $this->getKey(), $fileUrl);
    }

    /**
     * Find a specific attachment belonging to this model instance by its hashed file_url or fail with 404.
     */
    public function findAttachmentOrFail(string $fileUrl): FileAttachment
    {
        return Attachment::findForModelOrFail($this, $this->getKey(), $fileUrl);
    }

    /**
     * Stream (view inline) an attachment belonging to this model instance.
     */
    public function streamAttachment(string $fileUrl): StreamedResponse
    {
        return Attachment::stream($this->findAttachmentOrFail($fileUrl));
    }

    /**
     * Download an attachment belonging to this model instance.
     */
    public function downloadAttachment(string $fileUrl): StreamedResponse
    {
        return Attachment::download($this->findAttachmentOrFail($fileUrl));
    }

    /**
     * Delete a specific attachment belonging to this model instance transactionally.
     */
    public function deleteAttachment(string $fileUrl): bool
    {
        return Attachment::deleteForModelByUrl($this, $this->getKey(), $fileUrl);
    }

    /**
     * Delete all attachments associated with this model instance transactionally.
     */
    public function deleteAttachments(): int
    {
        return Attachment::deleteForModel($this, $this->getKey());
    }
}
