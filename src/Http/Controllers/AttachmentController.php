<?php

namespace Rft\LaravelAttachment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rft\LaravelAttachment\Facades\Attachment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Stream (view inline) or download an attachment using its hashed file_url.
     */
    public function show(Request $request, string $file_url): StreamedResponse
    {
        $download = $request->boolean('download', false);

        return Attachment::response($file_url, $download);
    }
}
