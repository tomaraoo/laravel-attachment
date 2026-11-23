<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Table Name
    |--------------------------------------------------------------------------
    |
    | The table used as the single source of truth for all file attachments.
    |
    */
    'table' => env('RFT_ATTACHMENT_TABLE', 'file-attachement'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disk & Directory
    |--------------------------------------------------------------------------
    |
    | Files are stored in storage/app/private by default using the "private"
    | disk. If the disk is not defined in config/filesystems.php, the package
    | automatically registers a local disk rooted at storage_path('app/private').
    |
    */
    'disk' => env('RFT_ATTACHMENT_DISK', 'private'),

    'storage_root' => storage_path('app/private'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the optional built-in streaming/downloading route keyed by the
    | hashed file_url token. Set 'enabled' to false if you prefer to declare
    | streaming and downloading exclusively inside your own controllers.
    |
    */
    'routes' => [
        'enabled' => env('RFT_ATTACHMENT_ROUTES_ENABLED', true),
        'prefix' => env('RFT_ATTACHMENT_PREFIX', 'attachments'),
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Validation Defaults
    |--------------------------------------------------------------------------
    |
    | Maximum file size in kilobytes (default: 10240 KB = 10 MB) and allowed
    | MIME types / extensions. Set allowed_mimes to null or ['*'] to allow all.
    |
    */
    'max_file_size' => env('RFT_ATTACHMENT_MAX_SIZE', 10240),

    'allowed_mimes' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg',
        'txt', 'zip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Non-Viewable File Extensions
    |--------------------------------------------------------------------------
    |
    | Extensions that cannot be previewed inline in the browser (e.g. Excel
    | workbooks). Used by FileAttachment::getIsViewableAttribute().
    |
    */
    'non_viewable_extensions' => [
        'xls', 'xlsx', 'xlsm', 'xlsb',
    ],

];
