<?php

namespace Rft\LaravelAttachment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Rft\LaravelAttachment\Facades\Attachment;
use Rft\LaravelAttachment\Models\FileAttachment;

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
     * Delete all attachments associated with this model instance transactionally.
     */
    public function deleteAttachments(): int
    {
        return Attachment::deleteForModel($this, $this->getKey());
    }
}
