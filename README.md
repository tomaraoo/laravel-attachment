# rft/laravel-attachment

Headless single source of truth transactional file attachment package for Laravel 11.x / 12.x.

This package ships **no Blade views, UI widgets, or display pages** so your application's existing page designs remain completely untouched. Simply add the `HasAttachments` trait to any Eloquent model and manage attachments from your own controllers and models.

## Features
- **Single Source of Truth Table**: `file-attachement`
  - `id`
  - `model` (e.g. `App\Models\Senior`)
  - `model_id` (target record ID)
  - `file_name` (original file name)
  - `file_url` (unique SHA-256 hash serving as the access token)
  - `user_id` (uploader user ID)
  - `created_at`
- **Private Storage**: Stores all physical files in `storage/app/private/{file_url}`.
- **Strictly Transactional**: Insert and delete only. Updating attachment records is blocked at the model level, and all file writes/removals run inside database transactions.
- **Headless / Zero Views**: No views, no demo pages, no forced upload/delete routes—only model/facade APIs and streaming/downloading.

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

### 2. Saving, Reading, Streaming & Deleting from Your Own Controller

```php
// Attach single file
$attachment = $senior->attachFile($request->file('document'));

// Attach multiple files from an HTTP request input
$attachments = $senior->attachFromRequest($request, 'attachments');

// Query all attachments for a model
$files = $senior->attachments;

// Stream (inline view) or download scoped to the model in your own controller
public function viewDocument(Senior $senior, string $fileUrl)
{
    return $senior->streamAttachment($fileUrl);
}

public function downloadDocument(Senior $senior, string $fileUrl)
{
    return $senior->downloadAttachment($fileUrl);
}

// Or directly from a FileAttachment instance
$attachment->stream();
$attachment->download();

// Delete a single attachment scoped to the model (transactional)
$senior->deleteAttachment($fileUrl);

// Delete all attachments for the model (transactional)
$senior->deleteAttachments();
```

### 3. Programmatic Usage (`Attachment` Facade)

```php
use Rft\LaravelAttachment\Facades\Attachment;

// Upload single file transactionally
$attachment = Attachment::store($request->file('document'), $senior);

// Upload multiple files from request
$attachments = Attachment::storeFromRequest($request, $senior, $senior->id, 'attachments');

// Stream or download by hashed file_url
return Attachment::stream($attachment->file_url);
return Attachment::download($attachment->file_url);

// Delete transactionally by hashed file_url
Attachment::deleteByUrl($attachment->file_url);
```

### 4. Built-in Streaming/Downloading Route (Optional)

By default, the package registers a single read-only route for streaming/downloading via the hashed `file_url`:
- `GET /attachments/{file_url}` — stream inline (`$attachment->view_url`)
- `GET /attachments/{file_url}?download=1` — download (`$attachment->download_url`)

If you prefer to declare streaming/downloading strictly inside your own model/controller routes, disable the built-in route in `config/laravel-attachment.php` (or `.env`):

```env
RFT_ATTACHMENT_ROUTES_ENABLED=false
```
