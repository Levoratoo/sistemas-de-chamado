@props(['ticket', 'compact' => false])

@php
    $user = auth()->user();
    $isFinalized = $ticket->status === \App\Models\Ticket::STATUS_FINALIZED;
    $alreadyAssignedToOther = $ticket->assignee_id && $ticket->assignee_id !== ($user->id ?? null);
    $assignDisabled = $alreadyAssignedToOther || $isFinalized;
    $assignMessage = $isFinalized ? 'Chamado já está finalizado.' : ($alreadyAssignedToOther ? 'Chamado já atribuído a outro atendente.' : null);
@endphp

@if($isFinalized)
    <div class="rounded-lg bg-green-50 border border-green-200 p-4">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-green-800">Chamado Finalizado</p>
                @if($ticket->resolved_at)
                    <p class="text-xs text-green-600">Finalizado em {{ $ticket->resolved_at->format('d/m/Y H:i') }}</p>
                @endif
                @if($ticket->resolver)
                    <p class="text-xs text-green-600">Por {{ $ticket->resolver->name }}</p>
                @endif
            </div>
        </div>
    </div>
@else
<div x-data="{ modal: null, submitting: false, showMenu: false }" @open-modal.window="modal = $event.detail; submitting = false" class="{{ $compact ? 'space-y-2' : 'space-y-4' }}">
    <div class="{{ $compact ? 'flex flex-wrap items-center gap-3 text-sm' : 'flex items-center justify-between' }}">
        <!-- Botão Assumir sempre visível -->
        <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="{{ $compact ? 'inline-block' : 'flex-1' }}">
            @csrf
            <button type="submit" class="{{ $compact ? 'inline-flex items-center text-primary-600 hover:text-primary-900 font-medium disabled:text-gray-400 disabled:hover:text-gray-400' : 'btn btn-primary w-full' }}" @disabled($assignDisabled) @if($assignMessage) title="{{ $assignMessage }}" @endif>
                Assumir
            </button>
        </form>

        @if(!$compact)
            <!-- Menu dos três pontinhos com todas as outras ações -->
            <div class="relative ml-4">
                <button type="button" @click="showMenu = !showMenu" class="inline-flex items-center p-2 text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </button>
                <div x-show="showMenu" @click.away="showMenu = false" x-cloak class="absolute right-0 top-full mt-1 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                    <!-- Ações de Status -->
                    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        Mudar Status
                    </div>
                    
                    <button type="button" @click="showMenu = false; modal = 'comment'; submitting = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Comentário
                    </button>
                    
                    <button type="button" @click="showMenu = false; modal = 'await'; submitting = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                        Marcar Aguardando
                    </button>
                    
                    <button type="button" @click="showMenu = false; modal = 'finalize'; submitting = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        Finalizar
                    </button>
                    
                    <!-- Separador -->
                    <div class="border-t border-gray-100 my-1"></div>
                    
                    <!-- Ações Gerais -->
                    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        Ações
                    </div>
                    
                    <button type="button" @click="showMenu = false; modal = 'delegate'; submitting = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Delegar chamado
                    </button>
                    
                    @if($ticket->assignee_id)
                        <button type="button" @click="showMenu = false; modal = 'returnQueue'; submitting = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                            Enviar para fila
                        </button>
                    @endif
                    
                    <button type="button" @click="showMenu = false; modal = 'attach'; submitting = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        Anexar arquivo
                    </button>
                </div>
            </div>
        @endif
    </div>

    <template x-if="modal === 'comment'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Enviar mensagem ao solicitante</h3>
                <form method="POST" action="{{ route('tickets.comment', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <label for="comment-message" class="sr-only">Mensagem</label>
                    <textarea id="comment-message" name="message" rows="4" required class="form-textarea w-full" placeholder="Descreva a mensagem para o solicitante"></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="submitting" x-text="submitting ? 'Enviando...' : 'Enviar'">
                            Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-if="modal === 'await'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Marcar como aguardando</h3>
                <form method="POST" action="{{ route('tickets.await', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <label for="await-reason" class="sr-only">Motivo</label>
                    <textarea id="await-reason" name="reason" rows="3" class="form-textarea w-full" placeholder="Informe o que esta pendente (opcional)"></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="submitting" x-text="submitting ? 'Salvando...' : 'Confirmar'">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-if="modal === 'finalize'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Finalizar chamado</h3>
                <form method="POST" action="{{ route('tickets.finalize', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <label for="finalize-summary" class="sr-only">Resumo da solução</label>
                    <textarea id="finalize-summary" name="resolution_summary" rows="5" required class="form-textarea w-full" placeholder="Descreva o que foi feito para resolver o chamado"></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" :disabled="submitting" x-text="submitting ? 'Finalizando...' : 'Finalizar'">
                            Finalizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-if="modal === 'delegate'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Delegar chamado</h3>
                <form method="POST" action="{{ route('tickets.delegate', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <label for="delegate-assignee" class="block text-sm font-medium text-gray-700 mb-2">Selecione o usuário</label>
                    <select id="delegate-assignee" name="assignee_id" required class="form-select w-full">
                        <option value="">Selecione um usuário...</option>
                        @foreach(\App\Services\TicketService::eligibleAssignees($ticket, auth()->user()) as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="submitting" x-text="submitting ? 'Delegando...' : 'Delegar'">
                            Delegar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-if="modal === 'returnQueue'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Enviar para fila</h3>
                <p class="text-sm text-gray-600 mb-4">O chamado será devolvido para a fila e ficará disponível para outros atendentes assumirem.</p>
                <form method="POST" action="{{ route('tickets.returnToQueue', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <label for="return-reason" class="block text-sm font-medium text-gray-700 mb-2">Motivo (opcional)</label>
                    <textarea id="return-reason" name="reason" rows="3" class="form-textarea w-full" placeholder="Informe o motivo (opcional)"></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="submitting" x-text="submitting ? 'Enviando...' : 'Confirmar'">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-if="modal === 'attach'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Anexar arquivos</h3>
                <form method="POST" action="{{ route('tickets.attachments.storeFromShow', $ticket) }}" enctype="multipart/form-data" x-on:submit="submitting = true">
                    @csrf
                    <label for="attach-files" class="block text-sm font-medium text-gray-700 mb-2">Selecione os arquivos</label>
                    <input type="file" id="attach-files" name="attachments[]" multiple required class="form-input w-full" accept="application/pdf,image/*,text/plain,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                    <p class="mt-2 text-xs text-gray-500">Máximo 10MB por arquivo. Tipos: PDF, imagens, texto, ZIP, Word, Excel</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="submitting" x-text="submitting ? 'Anexando...' : 'Anexar'">
                            Anexar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Em Análise -->
    <template x-if="modal === 'analysis'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Marcar como Em Análise</h3>
                <p class="text-sm text-gray-600 mb-4">O chamado será marcado como em análise e ficará pausado até que a análise seja concluída.</p>
                <form method="POST" action="{{ route('tickets.markAnalysis', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <textarea name="reason" rows="3" class="form-textarea w-full" placeholder="Motivo da análise (opcional)"></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">Cancelar</button>
                        <button type="submit" class="btn btn-warning" :disabled="submitting" x-text="submitting ? 'Marcando...' : 'Marcar em Análise'">Marcar em Análise</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Aguardando Terceiros -->
    <template x-if="modal === 'thirdParty'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aguardando Terceiros</h3>
                <p class="text-sm text-gray-600 mb-4">O chamado será marcado como aguardando resposta de terceiros.</p>
                <form method="POST" action="{{ route('tickets.markThirdParty', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <textarea name="reason" rows="3" class="form-textarea w-full" placeholder="Detalhes sobre o que está sendo aguardado (opcional)"></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">Cancelar</button>
                        <button type="submit" class="btn btn-orange" :disabled="submitting" x-text="submitting ? 'Marcando...' : 'Marcar Aguardando'">Marcar Aguardando</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Retorno do Solicitante -->
    <template x-if="modal === 'resume'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Retorno do Solicitante</h3>
                <p class="text-sm text-gray-600 mb-4">O chamado será retomado após o retorno do solicitante.</p>
                <form method="POST" action="{{ route('tickets.resume', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <textarea name="comment" rows="3" class="form-textarea w-full" placeholder="Comentário sobre o retorno (opcional)"></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">Cancelar</button>
                        <button type="submit" class="btn btn-primary" :disabled="submitting" x-text="submitting ? 'Retomando...' : 'Retomar'">Retomar</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Cancelar -->
    <template x-if="modal === 'cancel'">
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/60" @click.self="modal = null; submitting = false" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancelar chamado</h3>
                <p class="text-sm text-gray-600 mb-4">O chamado será cancelado e não poderá ser reaberto.</p>
                <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" x-on:submit="submitting = true">
                    @csrf
                    <textarea name="reason" rows="3" required class="form-textarea w-full" placeholder="Motivo do cancelamento"></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="modal = null; submitting = false">Não cancelar</button>
                        <button type="submit" class="btn btn-danger" :disabled="submitting" x-text="submitting ? 'Cancelando...' : 'Cancelar'">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endif
