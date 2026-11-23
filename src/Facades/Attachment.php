<?php

namespace Rft\LaravelAttachment\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Rft\LaravelAttachment\AttachmentManager;
use Rft\LaravelAttachment\Models\FileAttachment;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @method static string diskName()
 * @method static string generateHashedUrl(string $originalName, string $modelClass)
 * @method static FileAttachment store(UploadedFile $file, Model|string $model, int|string|null $modelId = null, ?int $userId = null)
 * @method static Collection<int, FileAttachment> storeMany(iterable $files, Model|string $model, int|string|null $modelId = null, ?int $userId = null)
 * @method static Collection<int, FileAttachment> storeFromRequest(Request $request, Model|string $model, int|string|null $modelId = null, string $inputName = 'attachments', ?int $userId = null)
 * @method static FileAttachment|null findByUrl(string $fileUrl)
 * @method static FileAttachment findByUrlOrFail(string $fileUrl)
 * @method static FileAttachment|null findForModel(Model|string $model, int|string|null $modelId, string $fileUrl)
 * @method static FileAttachment findForModelOrFail(Model|string $model, int|string|null $modelId, string $fileUrl)
 * @method static Collection<int, FileAttachment> forModel(Model|string $model, int|string|null $modelId = null)
 * @method static StreamedResponse response(FileAttachment|string $attachment, bool $download = false)
 * @method static StreamedResponse stream(FileAttachment|string $attachment)
 * @method static StreamedResponse download(FileAttachment|string $attachment)
 * @method static bool deleteByUrl(string $fileUrl)
 * @method static bool deleteForModelByUrl(Model|string $model, int|string|null $modelId, string $fileUrl)
 * @method static int deleteForModel(Model|string $model, int|string|null $modelId = null)
 * @method static array{0: string, 1: int|string|null} resolveModelAndId(Model|string $model, int|string|null $modelId = null)
 *
 * @see \Rft\LaravelAttachment\AttachmentManager
 */
class Attachment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AttachmentManager::class;
    }
}
