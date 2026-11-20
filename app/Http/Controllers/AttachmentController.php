<?php

namespace App\Http\Controllers;

use App\Models\IssueAttachment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function show(IssueAttachment $attachment): StreamedResponse
    {
        $attachment->loadMissing('issue');

        Gate::authorize('view', $attachment);

        abort_unless(
            Storage::disk($attachment->disk)->exists($attachment->file_path),
            404,
            'Attachment file not found.'
        );

        return Storage::disk($attachment->disk)->response(
            $attachment->file_path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }
}
