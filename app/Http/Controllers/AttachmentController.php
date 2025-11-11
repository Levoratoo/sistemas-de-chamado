<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        // Validação básica do Laravel
        $request->validate([
            'attachment' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif',
            ],
        ]);

        // Verificar se o usuário pode anexar arquivos neste ticket
        if (!Auth::user()->canManageTickets() && 
            $ticket->requester_id !== Auth::id() && 
            $ticket->assignee_id !== Auth::id()) {
            abort(403);
        }

        $file = $request->file('attachment');
        
        // Validação adicional de segurança
        $this->validateFileSecurity($file);

        $filename = Str::random(10) . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('tickets/' . $ticket->id, $filename, 'local');
        
        $ticket->attachments()->create([
            'user_id' => Auth::id(),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Anexo adicionado com sucesso!');
    }

    /**
     * Validar segurança do arquivo: extensão, MIME type e tipos perigosos
     */
    private function validateFileSecurity($file): void
    {
        // Validar extensão por nome do arquivo
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedExtensions, true)) {
            abort(422, 'A extensão do arquivo não é permitida.');
        }
        
        // Verificar correspondência entre extensão e MIME type
        $mimeType = $file->getMimeType();
        $validMimeTypes = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain', 'text/html'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
        ];
        
        if (isset($validMimeTypes[$extension])) {
            if (!in_array($mimeType, $validMimeTypes[$extension], true)) {
                abort(422, 'O conteúdo do arquivo não corresponde à extensão declarada.');
            }
        }
        
        // Bloquear extensões perigosas mesmo que disfarçadas
        $dangerousExtensions = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'sh', 'bin', 'msi', 'dll'];
        if (in_array($extension, $dangerousExtensions, true)) {
            abort(422, 'Tipo de arquivo não permitido por segurança.');
        }
        
        // Verificar se não é um arquivo executável disfarçado
        // (validar primeiros bytes do arquivo em produção - opcional)
    }

    public function download(Ticket $ticket, $attachmentId)
    {
        $attachment = $ticket->attachments()->findOrFail($attachmentId);
        
        // Verificar se o usuário pode baixar este anexo
        if (!Auth::user()->canManageTickets() && 
            $ticket->requester_id !== Auth::id() && 
            $ticket->assignee_id !== Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($attachment->path)) {
            abort(404);
        }

        return Storage::disk('local')->download($attachment->path, $attachment->filename);
    }

    public function destroy(Ticket $ticket, $attachmentId)
    {
        $attachment = $ticket->attachments()->findOrFail($attachmentId);
        
        // Verificar se o usuário pode deletar este anexo
        if (!Auth::user()->canManageTickets() && $attachment->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Anexo removido com sucesso!');
    }
}







