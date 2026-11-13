<?php

namespace Rft\LaravelAttachment\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Rft\LaravelAttachment\Facades\Attachment;
use Rft\LaravelAttachment\Models\FileAttachment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Sample one-page test & playground for rft/laravel-attachment.
     */
    public function demo(Request $request): View
    {
        $tableName = config('laravel-attachment.table', 'file-attachement');

        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('model')->comment('Fully qualified model class name, e.g. App\\Models\\Senior');
                $table->unsignedBigInteger('model_id')->nullable()->comment('Target model record ID');
                $table->string('file_name')->comment('Original uploaded file name');
                $table->string('file_url', 128)->unique()->comment('Hashed URL token serving as the single access point for view and delete');
                $table->unsignedBigInteger('user_id')->nullable()->index()->comment('Authenticated user ID who uploaded the attachment');
                $table->timestamp('created_at')->useCurrent();

                $table->index(['model', 'model_id'], 'file_attachement_model_index');
            });
        }

        $sampleModel = $request->query('model', 'App\\Models\\Senior');
        $sampleModelId = (int) $request->query('model_id', 1001);

        $allRecords = FileAttachment::query()->orderByDesc('id')->get();

        return view('laravel-attachment::demo', [
            'tableName' => $tableName,
            'diskName' => Attachment::diskName(),
            'storageRoot' => config('laravel-attachment.storage_root', storage_path('app/private')),
            'sampleModel' => $sampleModel,
            'sampleModelId' => $sampleModelId,
            'allRecords' => $allRecords,
        ]);
    }

    /**
     * Test endpoint to verify that updating an attachment record is strictly blocked.
     */
    public function testImmutability(string $file_url): JsonResponse
    {
        $attachment = Attachment::findByUrlOrFail($file_url);

        try {
            $attachment->update(['file_name' => 'hacked-name.pdf']);

            return response()->json([
                'status' => 'error',
                'message' => 'Update unexpectedly succeeded!',
            ], 500);
        } catch (LogicException $e) {
            return response()->json([
                'status' => 'blocked',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * View (inline stream) or download an attachment using its hashed file_url.
     */
    public function show(Request $request, string $file_url): StreamedResponse
    {
        $download = $request->boolean('download', false);

        return Attachment::response($file_url, $download);
    }

    /**
     * Store one or more uploaded attachments transactionally.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $maxSize = (int) config('laravel-attachment.max_file_size', 10240);
        $allowedMimes = config('laravel-attachment.allowed_mimes', []);

        $fileRules = ['required', 'file', "max:{$maxSize}"];
        if (! empty($allowedMimes) && $allowedMimes !== ['*']) {
            $fileRules[] = 'mimes:' . implode(',', $allowedMimes);
        }

        if ($request->hasFile('files')) {
            $request->validate([
                'model' => ['required', 'string', 'max:255'],
                'model_id' => ['nullable'],
                'files' => ['required', 'array', 'min:1'],
                'files.*' => $fileRules,
            ]);
            $uploadedFiles = $request->file('files');
        } else {
            $request->validate([
                'model' => ['required', 'string', 'max:255'],
                'model_id' => ['nullable'],
                'file' => $fileRules,
            ]);
            $uploadedFiles = [$request->file('file')];
        }

        $attachments = Attachment::storeMany(
            $uploadedFiles,
            $request->input('model'),
            $request->input('model_id'),
            $request->user()?->getAuthIdentifier()
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Attachment(s) uploaded successfully.',
                'data' => $attachments->map(fn ($item) => [
                    'id' => $item->id,
                    'model' => $item->model,
                    'model_id' => $item->model_id,
                    'file_name' => $item->file_name,
                    'file_url' => $item->file_url,
                    'user_id' => $item->user_id,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                    'view_url' => $item->view_url,
                    'download_url' => $item->download_url,
                    'delete_url' => $item->delete_url,
                    'is_image' => $item->is_image,
                    'extension' => $item->extension,
                ])->values(),
            ], 201);
        }

        return back()->with('success', 'Attachment(s) uploaded successfully.');
    }

    /**
     * Delete an attachment transactionally using its hashed file_url.
     */
    public function destroy(Request $request, string $file_url): JsonResponse|RedirectResponse
    {
        Attachment::deleteByUrl($file_url);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Attachment deleted successfully.',
                'file_url' => $file_url,
            ]);
        }

        return back()->with('success', 'Attachment deleted successfully.');
    }
}
