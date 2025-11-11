<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tickets\AttachmentFromShowRequest;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Services\TicketEventService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketAttachmentController extends Controller
{
    public function download(Ticket $ticket, TicketAttachment $attachment)
    {
        abort_unless($attachment->ticket_id === $ticket->id, 404);

        Gate::authorize('view', $ticket);

        $disk = Storage::disk('local');

        if (!$disk->exists($attachment->path)) {
            abort(404, 'Arquivo nao encontrado.');
        }

        $downloadName = $attachment->filename ?: basename($attachment->path);

        return $disk->download($attachment->path, $downloadName, [
            'Content-Type' => $attachment->mime ?? 'application/octet-stream',
        ]);
    }

    public function storeFromShow(AttachmentFromShowRequest $request, Ticket $ticket)
    {
        $this->authorize('work', $ticket);

        if (!$request->hasFile('attachments')) {
            return back()->with('error', 'Nenhum arquivo enviado.');
        }

        foreach ($request->file('attachments') as $file) {
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
                'user_id' => auth()->id(),
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);

            TicketEventService::log(
                $ticket,
                auth()->user(),
                'attachment_added',
                null,
                null,
                [
                    'attachment_id' => $attachment->id,
                    'attachment_name' => $attachment->filename,
                    'mime' => $attachment->mime,
                    'size' => $attachment->size,
                ]
            );
        }

        return back()->with('success', 'Arquivo(s) anexado(s) com sucesso.');
    }
}
