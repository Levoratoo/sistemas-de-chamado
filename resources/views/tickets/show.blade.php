@extends('layouts.app')

@section('content')
@once
    <style>
        [x-cloak] { display: none; }
    </style>
@endonce

<div class="py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <a href="javascript:history.back()" class="text-primary-600 hover:text-primary-800 font-medium">&larr; Voltar</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('tickets.index') }}" class="hover:text-gray-700">Meus chamados</a>
                <span class="text-gray-300">|</span>
                <span class="text-gray-700">Codigo</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-primary-50 text-primary-700">{{ $ticket->code }}</span>
            </div>
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="inline-flex items-center px-3 py-1 rounded-full {{ $ticket->status === \App\Models\Ticket::STATUS_FINALIZED ? 'bg-green-100 text-green-800 font-semibold' : 'bg-gray-100 text-gray-700' }}">
                    Status: {{ $ticket->status_label }}
                    @if($ticket->status === \App\Models\Ticket::STATUS_FINALIZED)
                        <svg class="ml-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </span>
                @if($ticket->area)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">Departamento: {{ $ticket->area->name }}</span>
                @endif
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700">Criado em {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl p-6">
            <div class="grid gap-4 md:grid-cols-2 text-sm text-gray-700">
                <div>
                    <span class="text-gray-500">Solicitante:</span>
                    <span class="font-semibold text-gray-900">{{ $ticket->requester->name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Título:</span>
                    <span class="font-semibold text-gray-900">{{ $ticket->title }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Atribuído a:</span>
                    <span class="font-semibold text-gray-900">{{ $ticket->assignee?->name ?? 'Ainda não definido' }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @if($ticket->started_at)
                        <span><span class="text-gray-500">Início atendimento:</span> {{ $ticket->started_at->format('d/m/Y H:i') }}</span>
                    @endif
                    @if($ticket->first_response_at)
                        <span><span class="text-gray-500">Primeira resposta:</span> {{ $ticket->first_response_at->format('d/m/Y H:i') }}</span>
                    @endif
                    @if($ticket->resolved_at)
                        <span><span class="text-gray-500">Finalizado em:</span> {{ $ticket->resolved_at->format('d/m/Y H:i') }}</span>
                    @endif
                    @if($ticket->resolver)
                        <span><span class="text-gray-500">Finalizado por:</span> {{ $ticket->resolver->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white shadow rounded-2xl p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-3">Descrição</h2>
                    <div class="prose max-w-none text-gray-800 whitespace-pre-line">
                        {{ $ticket->description }}
                    </div>
                </div>

                @if($ticket->request_type === 'reembolso')
                    <div class="bg-white shadow rounded-2xl p-6 border border-purple-100">
                        <h2 class="text-base font-semibold text-gray-900 mb-4">Informações de Reembolso</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($ticket->company)
                                <div>
                                    <span class="text-sm text-gray-500">Empresa:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->company }}</p>
                                </div>
                            @endif
                            @if($ticket->cost_center)
                                <div>
                                    <span class="text-sm text-gray-500">Centro de Custo:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->cost_center }}</p>
                                </div>
                            @endif
                            @if($ticket->approver)
                                <div>
                                    <span class="text-sm text-gray-500">Aprovador:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->approver->name }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_type)
                                <div>
                                    <span class="text-sm text-gray-500">Tipo de Pagamento:</span>
                                    <p class="mt-1 font-semibold text-gray-900">
                                        @if($ticket->payment_type === 'transferencia') Transferência Bancária
                                        @elseif($ticket->payment_type === 'pix') PIX
                                        @elseif($ticket->payment_type === 'boleto') Boleto
                                        @endif
                                    </p>
                                </div>
                            @endif
                            @if($ticket->payment_amount)
                                <div>
                                    <span class="text-sm text-gray-500">Valor:</span>
                                    <p class="mt-1 font-semibold text-gray-900">R$ {{ number_format($ticket->payment_amount, 2, ',', '.') }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_date)
                                <div>
                                    <span class="text-sm text-gray-500">Data de Pagamento:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->payment_date->format('d/m/Y') }}</p>
                                </div>
                            @endif
                        </div>
                        @if($ticket->bank_data)
                            <div class="mt-4">
                                <span class="text-sm text-gray-500">Dados Bancários:</span>
                                <p class="mt-1 text-gray-800 whitespace-pre-line">{{ $ticket->bank_data }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if($ticket->request_type === 'adiantamento')
                    <div class="bg-white shadow rounded-2xl p-6 border border-orange-100">
                        <h2 class="text-base font-semibold text-gray-900 mb-4">Informações de Adiantamento</h2>
                        
                        @if($ticket->openedOnBehalfOf)
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <span class="text-sm text-gray-500">Aberto em nome de:</span>
                                <p class="mt-1 font-semibold text-gray-900">{{ $ticket->openedOnBehalfOf->name }}</p>
                            </div>
                        @endif
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($ticket->company)
                                <div>
                                    <span class="text-sm text-gray-500">Empresa:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->company }}</p>
                                </div>
                            @endif
                            @if($ticket->cost_center)
                                <div>
                                    <span class="text-sm text-gray-500">Centro de Custo:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->cost_center }}</p>
                                </div>
                            @endif
                            @if($ticket->approver)
                                <div>
                                    <span class="text-sm text-gray-500">Aprovador:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->approver->name }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_type)
                                <div>
                                    <span class="text-sm text-gray-500">Tipo de Pagamento:</span>
                                    <p class="mt-1 font-semibold text-gray-900">
                                        @if($ticket->payment_type === 'transferencia') Transferência Bancária
                                        @elseif($ticket->payment_type === 'pix') PIX
                                        @elseif($ticket->payment_type === 'boleto') Boleto
                                        @endif
                                    </p>
                                </div>
                            @endif
                            @if($ticket->payment_amount)
                                <div>
                                    <span class="text-sm text-gray-500">Valor:</span>
                                    <p class="mt-1 font-semibold text-gray-900">R$ {{ number_format($ticket->payment_amount, 2, ',', '.') }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_date)
                                <div>
                                    <span class="text-sm text-gray-500">Data de Pagamento:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->payment_date->format('d/m/Y') }}</p>
                                </div>
                            @endif
                        </div>
                        @if($ticket->bank_data)
                            <div class="mt-4">
                                <span class="text-sm text-gray-500">Dados Bancários:</span>
                                <p class="mt-1 text-gray-800 whitespace-pre-line">{{ $ticket->bank_data }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if($ticket->request_type === 'pagamento_geral')
                    <div class="bg-white shadow rounded-2xl p-6 border border-yellow-100">
                        <h2 class="text-base font-semibold text-gray-900 mb-4">Informações de Pagamento Geral</h2>
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($ticket->company)
                                <div>
                                    <span class="text-sm text-gray-500">Empresa:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->company }}</p>
                                </div>
                            @endif
                            @if($ticket->cost_center)
                                <div>
                                    <span class="text-sm text-gray-500">Centro de Custo:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->cost_center }}</p>
                                </div>
                            @endif
                            @if($ticket->approver)
                                <div>
                                    <span class="text-sm text-gray-500">Aprovador:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->approver->name }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_type)
                                <div>
                                    <span class="text-sm text-gray-500">Tipo de Pagamento:</span>
                                    <p class="mt-1 font-semibold text-gray-900">
                                        @if($ticket->payment_type === 'transferencia') Transferência Bancária
                                        @elseif($ticket->payment_type === 'pix') PIX
                                        @elseif($ticket->payment_type === 'boleto') Boleto
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                        @if($ticket->bank_data)
                            <div class="mt-4">
                                <span class="text-sm text-gray-500">Dados Bancários:</span>
                                <p class="mt-1 text-gray-800 whitespace-pre-line">{{ $ticket->bank_data }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if($ticket->request_type === 'rh')
                    <div class="bg-white shadow rounded-2xl p-6 border border-indigo-100">
                        <h2 class="text-base font-semibold text-gray-900 mb-4">Informações de RH</h2>
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($ticket->company)
                                <div>
                                    <span class="text-sm text-gray-500">Empresa:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->company }}</p>
                                </div>
                            @endif
                            @if($ticket->employee_code)
                                <div>
                                    <span class="text-sm text-gray-500">Código Funcionário:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->employee_code }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_amount)
                                <div>
                                    <span class="text-sm text-gray-500">Valor:</span>
                                    <p class="mt-1 font-semibold text-gray-900">R$ {{ number_format($ticket->payment_amount, 2, ',', '.') }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_date)
                                <div>
                                    <span class="text-sm text-gray-500">Data de Pagamento:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->payment_date->format('d/m/Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($ticket->request_type === 'contabilidade')
                    <div class="bg-white shadow rounded-2xl p-6 border border-red-100">
                        <h2 class="text-base font-semibold text-gray-900 mb-4">Informações de Contabilidade</h2>
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($ticket->company)
                                <div>
                                    <span class="text-sm text-gray-500">Empresa:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->company }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_amount)
                                <div>
                                    <span class="text-sm text-gray-500">Valor:</span>
                                    <p class="mt-1 font-semibold text-gray-900">R$ {{ number_format($ticket->payment_amount, 2, ',', '.') }}</p>
                                </div>
                            @endif
                            @if($ticket->payment_date)
                                <div>
                                    <span class="text-sm text-gray-500">Data de Pagamento:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->payment_date->format('d/m/Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(in_array($ticket->request_type, ['devolucao_clientes', 'pagamento_importacoes']))
                    <div class="bg-white shadow rounded-2xl p-6 border {{ $ticket->request_type === 'devolucao_clientes' ? 'border-green-100' : 'border-blue-100' }}">
                        <h2 class="text-base font-semibold text-gray-900 mb-4">Informações {{ $ticket->request_type === 'devolucao_clientes' ? 'de Devolução de Clientes' : 'de Pagamento de Importações' }}</h2>
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($ticket->company)
                                <div>
                                    <span class="text-sm text-gray-500">Empresa:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->company }}</p>
                                </div>
                            @endif
                            @if($ticket->cost_center)
                                <div>
                                    <span class="text-sm text-gray-500">Centro de Custo:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->cost_center }}</p>
                                </div>
                            @endif
                            @if($ticket->approver)
                                <div>
                                    <span class="text-sm text-gray-500">Aprovador:</span>
                                    <p class="mt-1 font-semibold text-gray-900">{{ $ticket->approver->name }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($ticket->resolution_summary)
                    <div class="bg-white shadow rounded-2xl p-6 border border-emerald-100">
                        <h2 class="text-base font-semibold text-gray-900 mb-3">Resumo da solução</h2>
                        <div class="text-gray-800 whitespace-pre-line">{{ $ticket->resolution_summary }}</div>
                    </div>
                @endif

                <div class="bg-white shadow rounded-2xl p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Linha do tempo</h2>
                    @if($events->isEmpty())
                        <p class="text-sm text-gray-500">Nenhum evento registrado ainda.</p>
                    @else
                        <ul class="space-y-4">
                            @foreach($events as $event)
                                @php
                                    $comment = null;
                                    if ($event->type === 'commented') {
                                        $commentId = $event->meta['comment_id'] ?? null;
                                        if ($commentId) {
                                            $comment = $ticket->comments->firstWhere('id', $commentId);
                                        }
                                    }

                                    $label = match($event->type) {
                                        'created' => 'Chamado aberto',
                                        'assigned' => 'Atendimento assumido',
                                        'commented' => 'Comentário registrado',
                                        'status_changed' => 'Status atualizado',
                                        'finalized' => 'Chamado finalizado',
                                        'delegated' => 'Chamado delegado',
                                        'returned_to_queue' => 'Chamado devolvido para fila',
                                        'attachment_added' => 'Arquivo anexado',
                                        'marked_analysis' => 'Marcado como Em Análise',
                                        'marked_third_party' => 'Aguardando Terceiros',
                                        'resumed' => 'Retorno do Solicitante',
                                        'cancelled' => 'Chamado Cancelado',
                                        default => ucfirst(str_replace('_', ' ', $event->type)),
                                    };
                                @endphp
                                <li class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-primary-500"></span>
                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                                            <span class="font-semibold text-gray-800">{{ $label }}</span>
                                            @if($event->from_status || $event->to_status)
                                                <span class="text-xs text-gray-500">{{ $event->from_status ?? '-' }} &rarr; {{ $event->to_status ?? '-' }}</span>
                                            @endif
                                            <span class="text-xs text-gray-400">{{ $event->occurred_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        @if($event->user)
                                            <div class="text-xs text-gray-500">Por {{ $event->user->name }}</div>
                                        @endif
                                        @if(!empty($event->meta['reason']))
                                            <div class="mt-1 text-sm text-gray-700">Motivo: {{ $event->meta['reason'] }}</div>
                                        @endif
                                        @if($event->type === 'delegated' && !empty($event->meta['to_assignee_id']))
                                            @php
                                                $toUser = \App\Models\User::find($event->meta['to_assignee_id']);
                                            @endphp
                                            @if($toUser)
                                                <div class="mt-1 text-sm text-gray-700">Delegado para: {{ $toUser->name }}</div>
                                            @endif
                                        @endif
                                        @if($event->type === 'returned_to_queue' && !empty($event->meta['previous_assignee_id']))
                                            @php
                                                $previousUser = \App\Models\User::find($event->meta['previous_assignee_id']);
                                            @endphp
                                            @if($previousUser)
                                                <div class="mt-1 text-sm text-gray-700">Anteriormente atribuído a: {{ $previousUser->name }}</div>
                                            @endif
                                        @endif
                                        @if($event->type === 'attachment_added' && !empty($event->meta['attachment_name']))
                                            @php
                                                $attachmentId = $event->meta['attachment_id'] ?? null;
                                                $attachment = $ticket->attachments->firstWhere('id', $attachmentId);
                                            @endphp
                                            @if($attachment)
                                                <div class="mt-1 text-sm text-gray-700">
                                                    Arquivo: 
                                                    <a href="{{ route('tickets.attachments.download', [$ticket, $attachment]) }}" class="text-primary-600 hover:text-primary-800">
                                                        {{ $event->meta['attachment_name'] }}
                                                    </a>
                                                    @if(!empty($event->meta['size']))
                                                        <span class="text-gray-500">({{ round($event->meta['size'] / 1024, 2) }} KB)</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="mt-1 text-sm text-gray-700">Arquivo: {{ $event->meta['attachment_name'] }}</div>
                                            @endif
                                        @endif
                                        @if($comment)
                                            <div class="mt-2 rounded-lg bg-gray-50 p-3 text-sm text-gray-700 whitespace-pre-line">{{ $comment->body }}</div>
                                        @elseif(!empty($event->meta['message']))
                                            <div class="mt-2 rounded-lg bg-gray-50 p-3 text-sm text-gray-700 whitespace-pre-line">{{ $event->meta['message'] }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="md:col-span-1 space-y-6">
                <div class="bg-white shadow rounded-2xl p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Anexos</h2>
                    @if($ticket->attachments->isEmpty())
                        <p class="text-sm text-gray-500">Nenhum arquivo anexado.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($ticket->attachments as $attachment)
                                <li class="flex items-center justify-between rounded-xl bg-gray-50 px-3 py-2">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-800 truncate">{{ $attachment->filename }}</div>
                                        <div class="text-xs text-gray-500">{{ $attachment->mime }} &bull; {{ $attachment->size_formatted }}</div>
                                    </div>
                                    <a href="{{ route('tickets.attachments.download', [$ticket, $attachment]) }}" class="ml-3 inline-flex items-center rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                                        Baixar
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @can('work', $ticket)
                    <div class="bg-white shadow rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base font-semibold text-gray-900">Atendimento</h2>
                        </div>
                        @include('tickets.partials.work-actions', ['ticket' => $ticket])
                    </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

