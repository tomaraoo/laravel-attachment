# rft/laravel-attachment

Single source of truth transactional file attachment package and Yii2-style attachment widget for Laravel 12.x.

## Features
- **Single Source of Truth Table**: `file-attachement`
  - `id`
  - `model` (e.g. `App\Models\Senior`)
  - `model_id` (target record ID)
  - `file_name` (original file name)
  - `file_url` (unique SHA-256 hash serving as the URL access point for all views, downloads, and deletes)
  - `user_id` (uploader user ID)
  - `created_at`
- **Private Storage**: Stores all physical files in `storage/app/private/{file_url}`.
- **Strictly Transactional**: Insert and delete only. Updating attachment records is blocked at the model level, and all file writes/removals run inside database transactions.
- **Yii2-Style Attachment Widget**: Render via Blade Component (`<x-rft-attachment />`), Directive (`@attachmentWidget`), or static call (`AttachmentWidget::widget([...])`).

## Usage

### 1. Model Trait (`HasAttachments`)
Add `Rft\LaravelAttachment\Traits\HasAttachments` to any Eloquent model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Rft\LaravelAttachment\Traits\HasAttachments;

class Senior extends Model
{
    use HasAttachments;
}
```

### 2. Rendering the Attachment Widget in Blade

**Blade Component:**
```blade
{{-- Instant AJAX upload & delete for an existing model --}}
<x-rft-attachment :model="$senior" label="Supporting Documents" />

{{-- Or passing model class and ID explicitly --}}
<x-rft-attachment model="App\Models\Senior" :model-id="$senior->id" />

{{-- Read-only view mode --}}
<x-rft-attachment :model="$senior" :readonly="true" />
```

**Yii2-Style Static Widget Call:**
```blade
{!! \Rft\LaravelAttachment\Widgets\AttachmentWidget::widget([
    'model' => $senior,
    'label' => 'Senior Citizen Attachments',
]) !!}
```

### 3. Programmatic Usage (`Attachment` Facade)
```php
use Rft\LaravelAttachment\Facades\Attachment;

// Upload single file transactionally
$attachment = Attachment::store($request->file('document'), $senior);

// Upload multiple files from request
$attachments = Attachment::storeFromRequest($request, $senior, $senior->id, 'attachments');

// Access URLs (all keyed by hashed file_url)
$viewUrl = $attachment->view_url;         // GET /attachments/{file_url}
$downloadUrl = $attachment->download_url; // GET /attachments/{file_url}?download=1
$deleteUrl = $attachment->delete_url;     // DELETE /attachments/{file_url}

// Delete transactionally by hashed file_url
Attachment::deleteByUrl($attachment->file_url);
```
