<?php

namespace App\Helpers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadHelper
{
    /**
     * Processa e salva anexos de um ticket
     */
    public static function processAttachments(Ticket $ticket, array $files, int $userId): array
    {
        $uploadedAttachments = [];
        
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = Str::uuid()->toString() . '_' . $originalName;

            $path = Storage::disk('local')->putFileAs(
                'tickets/' . $ticket->id,
                $file,
                $storedName
            );

            $attachment = $ticket->attachments()->create([
                'user_id' => $userId,
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);

            $uploadedAttachments[] = $attachment;
        }

        return $uploadedAttachments;
    }
}











