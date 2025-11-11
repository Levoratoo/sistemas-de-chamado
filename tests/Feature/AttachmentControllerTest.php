<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed();
    }

    public function test_can_upload_valid_attachment(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $ticket = Ticket::factory()->create(['requester_id' => $user->id]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/attachments", [
                'attachment' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('ticket_attachments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'filename' => 'document.pdf',
        ]);

        Storage::disk('local')->assertExists(
            "tickets/{$ticket->id}/" . collect(Storage::disk('local')->allFiles("tickets/{$ticket->id}"))->first()
        );
    }

    public function test_rejects_invalid_file_types(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $ticket = Ticket::factory()->create(['requester_id' => $user->id]);

        $file = UploadedFile::fake()->create('script.exe', 100);

        $response = $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/attachments", [
                'attachment' => $file,
            ]);

        $response->assertStatus(422);
        
        $this->assertDatabaseMissing('ticket_attachments', [
            'ticket_id' => $ticket->id,
        ]);
    }

    public function test_rejects_files_exceeding_size_limit(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $ticket = Ticket::factory()->create(['requester_id' => $user->id]);

        // Criar arquivo maior que 10MB (10240 KB)
        $file = UploadedFile::fake()->create('large.pdf', 10250); // 10250 KB

        $response = $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/attachments", [
                'attachment' => $file,
            ]);

        $response->assertStatus(422);
        
        $this->assertDatabaseMissing('ticket_attachments', [
            'ticket_id' => $ticket->id,
        ]);
    }

    public function test_requester_can_download_attachment(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $ticket = Ticket::factory()->create(['requester_id' => $user->id]);
        
        $attachment = $ticket->attachments()->create([
            'user_id' => $user->id,
            'filename' => 'test.pdf',
            'path' => 'tickets/' . $ticket->id . '/test.pdf',
            'mime' => 'application/pdf',
            'size' => 100,
        ]);

        Storage::disk('local')->put($attachment->path, 'test content');

        $response = $this->actingAs($user)
            ->get("/tickets/{$ticket->id}/attachments/{$attachment->id}/download");

        $response->assertDownload();
        $response->assertHeader('Content-Disposition', 'attachment; filename="test.pdf"');
    }

    public function test_unauthorized_user_cannot_download_attachment(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory()->create(['requester_id' => $user->id]);
        
        $attachment = $ticket->attachments()->create([
            'user_id' => $user->id,
            'filename' => 'test.pdf',
            'path' => 'tickets/' . $ticket->id . '/test.pdf',
            'mime' => 'application/pdf',
            'size' => 100,
        ]);

        $response = $this->actingAs($otherUser)
            ->get("/tickets/{$ticket->id}/attachments/{$attachment->id}/download");

        $response->assertForbidden();
    }

    public function test_can_delete_own_attachment(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $ticket = Ticket::factory()->create(['requester_id' => $user->id]);
        
        $attachment = $ticket->attachments()->create([
            'user_id' => $user->id,
            'filename' => 'test.pdf',
            'path' => 'tickets/' . $ticket->id . '/test.pdf',
            'mime' => 'application/pdf',
            'size' => 100,
        ]);

        Storage::disk('local')->put($attachment->path, 'test content');

        $response = $this->actingAs($user)
            ->delete("/tickets/{$ticket->id}/attachments/{$attachment->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('ticket_attachments', [
            'id' => $attachment->id,
        ]);

        Storage::disk('local')->assertMissing($attachment->path);
    }

    public function test_validates_file_extension_matches_mime_type(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $ticket = Ticket::factory()->create(['requester_id' => $user->id]);

        // Tentar fazer upload de arquivo com extensão PDF mas conteúdo diferente
        $file = UploadedFile::fake()->image('fake.pdf');

        $response = $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/attachments", [
                'attachment' => $file,
            ]);

        // Deve rejeitar se a validação de MIME type estiver funcionando
        // O teste pode passar ou falhar dependendo de como o Laravel valida
        $response->assertStatus(422);
    }
}





