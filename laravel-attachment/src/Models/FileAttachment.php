<?php

namespace Rft\LaravelAttachment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use LogicException;

class FileAttachment extends Model
{
    /**
     * Disable updated_at timestamp since records are immutable (insert and delete only).
     */
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model',
        'model_id',
        'file_name',
        'file_url',
        'user_id',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'model_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array/JSON form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'view_url',
        'download_url',
        'delete_url',
        'is_image',
        'extension',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('laravel-attachment.table', 'file-attachement');
    }

    /**
     * Get the route key for the model (always use hashed file_url).
     */
    public function getRouteKeyName(): string
    {
        return 'file_url';
    }

    /**
     * Boot the model and enforce transactional immutability (insert & delete only).
     */
    protected static function booted(): void
    {
        static::creating(function (self $attachment) {
            if (empty($attachment->created_at)) {
                $attachment->created_at = now();
            }
        });

        static::updating(function () {
            throw new LogicException(
                'FileAttachment is transactional and immutable. Updating existing attachment records is not allowed; only insert and delete operations are permitted.'
            );
        });

        static::deleted(function (self $attachment) {
            $disk = config('laravel-attachment.disk', 'private');
            $storagePath = $attachment->getStoragePath();

            if (Storage::disk($disk)->exists($storagePath)) {
                Storage::disk($disk)->delete($storagePath);
            }
        });
    }

    /**
     * Prevent direct update calls on the query builder from model instance.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new LogicException(
            'FileAttachment is transactional and immutable. Updating existing attachment records is not allowed; only insert and delete operations are permitted.'
        );
    }

    /**
     * Polymorphic relation to the parent model (e.g., App\Models\Senior).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model', 'model_id');
    }

    /**
     * User who uploaded the attachment.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        return $this->belongsTo($userModel, 'user_id');
    }

    /**
     * Relative path inside storage/app/private where the physical file is stored.
     */
    public function getStoragePath(): string
    {
        return $this->file_url;
    }

    /**
     * Full filesystem path to the stored file in storage/app/private.
     */
    public function getFullPath(): string
    {
        $disk = config('laravel-attachment.disk', 'private');

        return Storage::disk($disk)->path($this->getStoragePath());
    }

    /**
     * Check whether the underlying file exists in storage/app/private.
     */
    public function fileExists(): bool
    {
        $disk = config('laravel-attachment.disk', 'private');

        return Storage::disk($disk)->exists($this->getStoragePath());
    }

    /**
     * URL access point for viewing/streaming the attachment inline.
     */
    public function getViewUrlAttribute(): string
    {
        return route('rft.attachments.show', ['file_url' => $this->file_url]);
    }

    /**
     * URL access point for downloading the attachment.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('rft.attachments.show', ['file_url' => $this->file_url, 'download' => 1]);
    }

    /**
     * URL access point for deleting the attachment.
     */
    public function getDeleteUrlAttribute(): string
    {
        return route('rft.attachments.destroy', ['file_url' => $this->file_url]);
    }

    /**
     * File extension derived from original file_name.
     */
    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    /**
     * Determine whether the file is an image for preview purposes.
     */
    public function getIsImageAttribute(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
    }
}
