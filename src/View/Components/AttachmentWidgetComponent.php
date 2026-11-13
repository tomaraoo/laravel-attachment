<?php

namespace Rft\LaravelAttachment\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Rft\LaravelAttachment\Facades\Attachment;
use Rft\LaravelAttachment\Widgets\AttachmentWidget;

class AttachmentWidgetComponent extends Component
{
    public string $widgetId;
    public string $modelClass;
    public int|string|null $modelId;
    public Collection $attachments;
    public bool $multiple;
    public bool $readonly;
    public bool $autoUpload;
    public string $inputName;
    public string $label;
    public string $accept;
    public int $maxFileSizeKb;
    public string $uploadUrl;

    public function __construct(
        Model|string|null $model = null,
        int|string|null $modelId = null,
        ?Collection $attachments = null,
        ?string $id = null,
        bool $multiple = true,
        bool $readonly = false,
        ?bool $autoUpload = null,
        ?string $inputName = null,
        string $label = 'File Attachments',
        ?string $accept = null,
        ?int $maxFileSize = null
    ) {
        [$this->modelClass, $this->modelId] = $model !== null
            ? Attachment::resolveModelAndId($model, $modelId)
            : ['', $modelId];

        $this->attachments = $attachments ?? (
            $this->modelClass !== '' && $this->modelId !== null
                ? Attachment::forModel($this->modelClass, $this->modelId)
                : collect()
        );

        $this->widgetId = $id ?? ('rft-attachment-widget-' . Str::random(8));
        $this->multiple = $multiple;
        $this->readonly = $readonly;
        $this->autoUpload = $autoUpload ?? ($this->modelId !== null);
        $this->inputName = $inputName ?? ($multiple ? 'attachments[]' : 'attachments');
        $this->label = $label;
        $this->accept = $accept ?? AttachmentWidget::defaultAcceptString();
        $this->maxFileSizeKb = $maxFileSize ?? (int) config('laravel-attachment.max_file_size', 10240);
        $this->uploadUrl = route('rft.attachments.store');
    }

    public function render(): View
    {
        return view('laravel-attachment::widget');
    }
}
