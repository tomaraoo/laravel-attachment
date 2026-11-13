<?php

namespace Rft\LaravelAttachment\Widgets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Rft\LaravelAttachment\Facades\Attachment;

class AttachmentWidget
{
    /**
     * Render the Yii2-style attachment widget.
     *
     * Usage:
     *   {!! \Rft\LaravelAttachment\Widgets\AttachmentWidget::widget(['model' => $senior]) !!}
     *   {!! AttachmentWidget::widget(['model' => 'App\Models\Senior', 'modelId' => 1]) !!}
     *
     * @param  array<string, mixed>  $config
     */
    public static function widget(array $config = []): string
    {
        $modelInput = $config['model'] ?? null;
        $modelId = $config['modelId'] ?? $config['model_id'] ?? null;

        [$modelClass, $resolvedModelId] = $modelInput !== null
            ? Attachment::resolveModelAndId($modelInput, $modelId)
            : ['', $modelId];

        /** @var Collection $attachments */
        $attachments = $config['attachments'] ?? (
            $modelClass !== '' && $resolvedModelId !== null
                ? Attachment::forModel($modelClass, $resolvedModelId)
                : collect()
        );

        $widgetId = $config['id'] ?? ('rft-attachment-widget-' . Str::random(8));
        $multiple = (bool) ($config['multiple'] ?? true);
        $readonly = (bool) ($config['readonly'] ?? false);
        $autoUpload = (bool) ($config['autoUpload'] ?? ($resolvedModelId !== null));
        $inputName = $config['inputName'] ?? ($multiple ? 'attachments[]' : 'attachments');
        $label = $config['label'] ?? 'File Attachments';
        $accept = $config['accept'] ?? self::defaultAcceptString();
        $maxFileSizeKb = (int) ($config['maxFileSize'] ?? config('laravel-attachment.max_file_size', 10240));

        return view('laravel-attachment::widget', [
            'widgetId' => $widgetId,
            'modelClass' => $modelClass,
            'modelId' => $resolvedModelId,
            'attachments' => $attachments,
            'multiple' => $multiple,
            'readonly' => $readonly,
            'autoUpload' => $autoUpload,
            'inputName' => $inputName,
            'label' => $label,
            'accept' => $accept,
            'maxFileSizeKb' => $maxFileSizeKb,
            'uploadUrl' => route('rft.attachments.store'),
        ])->render();
    }

    /**
     * Build the HTML accept attribute from configured allowed_mimes.
     */
    public static function defaultAcceptString(): string
    {
        $allowed = config('laravel-attachment.allowed_mimes', []);
        if (empty($allowed) || $allowed === ['*']) {
            return '*/*';
        }

        return implode(',', array_map(fn ($ext) => '.' . ltrim($ext, '.'), $allowed));
    }
}
