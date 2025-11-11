@extends('layouts.app')

@section('content')
@once
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        [x-cloak] { display: none; }

        .kanban-board {
            display: grid;
            grid-template-columns: repeat(4, minmax(280px, 1fr));
            gap: 1.75rem;
            padding: 1.5rem 0;
        }

        .kanban-column {
            position: relative;
            min-width: 0;
            display: flex;
            flex-direction: column;
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            min-height: 32rem;
            transition: all 0.2s ease;
        }

        .kanban-column:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .kanban-column.sortable-ghost {
            border: 2px dashed #3b82f6;
            background: #eff6ff;
        }

        .kanban-column__header {
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid #f3f4f6;
            background: linear-gradient(to bottom, #fafafa, #ffffff);
            border-radius: 1.25rem 1.25rem 0 0;
        }

        .kanban-column__body {
            flex: 1;
            padding: 1rem;
            padding-bottom: 2rem;
            overflow-y: auto;
            min-height: 400px;
            position: relative;
        }
        
        /* Espaço no final da coluna para facilitar arrastar */
        .kanban-drop-zone-spacer {
            min-height: 200px;
            width: 100%;
            pointer-events: none;
            position: relative;
        }
        
        /* Placeholders invisíveis para permitir drop em qualquer lugar */
        .kanban-drop-placeholder {
            display: none;
        }
        
        /* Quando arrastando, mostrar os placeholders */
        [data-kanban-dropzone]:has(.sortable-ghost) .kanban-drop-placeholder,
        [data-kanban-dropzone]:has(.sortable-drag) .kanban-drop-placeholder {
            display: block;
            opacity: 0;
            visibility: hidden;
            margin: 0;
            padding: 0;
        }

        .kanban-column__body::-webkit-scrollbar {
            width: 6px;
        }

        .kanban-column__body::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 9999px;
        }

        .kanban-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            margin-bottom: 0.75rem;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: #d1d5db;
        }

        .kanban-card.sortable-ghost {
            opacity: 0.5;
            border: 2px dashed #3b82f6;
            background: #eff6ff;
        }

        .kanban-card.sortable-drag {
            opacity: 0.8;
            transform: rotate(2deg);
        }

        .kanban-card .badge {
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.05em;
        }

        .kanban-card.card-green {
            border-color: rgba(34, 197, 94, 0.35);
            background: linear-gradient(150deg, rgba(220, 252, 231, 0.97) 0%, rgba(187, 247, 208, 0.97) 100%);
            box-shadow: 0 18px 35px -28px rgba(22, 101, 52, 0.4);
        }

    </style>
@endonce

@php
    $kanbanColumns = collect($columnsMeta)->map(function ($meta, $key) {
        return [
            'key' => $key,
            'label' => $meta['label'],
            'description' => $meta['description'],
            'bg' => $meta['bg'],
            'accent' => $meta['accent'],
        ];
    })->values();
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"
        x-data="kanbanBoard({
            columns: {{ $kanbanColumns->toJson() }},
            tickets: {{ $tickets->toJson() }},
            endpoints: {
                update: '{{ route('tickets.kanban.update-status', ['ticket' => '__TICKET__']) }}'
            },
            csrf: '{{ csrf_token() }}'
        })"
        x-init="init()"
    >
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Kanban de Chamados</h1>
                    <p class="text-sm text-gray-500">
                        Arraste os cartões entre as colunas para atualizar o status. Clique em um chamado para ver detalhes rápidos.
                    </p>
                </div>
                <a href="{{ route('request-areas.index') }}" class="btn btn-primary">
                    Novo Chamado
                </a>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="GET" action="{{ route('tickets.kanban') }}" class="flex flex-wrap items-end gap-4">
                    <div class="flex flex-col gap-1 w-full sm:w-64">
                        <label for="search" class="text-sm font-medium text-gray-700">Buscar</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            placeholder="Código ou título"
                            value="{{ $filters['search'] }}"
                            class="form-input"
                        >
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="assigned_to_me"
                            id="assigned_to_me"
                            value="1"
                            @checked($filters['assigned_to_me'])
                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                        >
                        <label for="assigned_to_me" class="text-sm text-gray-700">Somente atribuídos a mim</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="my_tickets"
                            id="my_tickets"
                            value="1"
                            @checked($filters['my_tickets'])
                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                        >
                        <label for="my_tickets" class="text-sm text-gray-700">Chamados que eu abri</label>
                    </div>

                    <div class="flex gap-2 w-full sm:w-auto sm:ml-auto">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('tickets.kanban') }}" class="btn btn-secondary">Limpar</a>
                    </div>
                </form>
            </div>
        </div>


        <div class="bg-gray-50 px-6 py-6">
            <div class="kanban-board" :key="renderKey">
                <template x-for="column in columns" :key="column.key">
                    <div class="kanban-column"
                        :data-status="column.key"
                        data-kanban-column
                    >
                        <div class="kanban-column__header">
                            <div class="h-2 rounded-full mb-3 opacity-90" :class="column.accent"></div>
                            <div class="flex items-center justify-between mb-2">
                                <h2 class="text-xl font-bold text-gray-900" x-text="column.label"></h2>
                                <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-sm font-bold text-gray-700 bg-gray-100"
                                x-text="getTickets(column.key).length">
                            </span>
                        </div>
                        <p class="text-sm text-gray-600" x-text="column.description"></p>
                        <div class="kanban-column__body"
                            data-kanban-dropzone
                            style="min-height: 600px;"
                        >
                        <template x-for="ticket in getTickets(column.key)" :key="ticket.id">
                            <div class="kanban-card cursor-move"
                                :data-ticket-id="ticket.id"
                                :class="[ticket.card_class || '', {'opacity-50': loadingTicket === ticket.id}]"
                                @click="openDetails(ticket)"
                            >
                                <div class="p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wide" x-text="ticket.code"></span>
                                        <div class="flex items-center gap-2">
                                            <span class="badge" :class="ticket.priority_badge" x-text="ticket.priority_label"></span>
                                        </div>
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-900 line-clamp-2" x-text="ticket.title"></h3>
                                    <div class="flex flex-col gap-1 text-xs text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span x-text="ticket.status_label"></span>
                                        </div>
                                        <div class="flex items-center gap-2" x-show="ticket.assignee">
                                            <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 018.828 16h6.344a4 4 0 013.707 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span x-text="ticket.assignee"></span>
                                        </div>
                                        <div class="flex items-center gap-2" x-show="ticket.requester">
                                            <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span>
                                                <span class="text-gray-400">Solicitante:</span>
                                                <span x-text="ticket.requester"></span>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2" x-show="ticket.updated_human">
                                            <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span x-text="'Atualizado ' + ticket.updated_human"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <!-- Espaço no final para permitir arrastar cards até o final -->
                        <div class="kanban-drop-zone-spacer"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Detalhes do chamado -->
        <div x-show="modals.details" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4">
                <div class="p-6 border-b border-gray-200 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold text-primary-600 uppercase" x-text="selectedTicket?.code"></p>
                        <h3 class="text-xl font-semibold text-gray-900 mt-1" x-text="selectedTicket?.title"></h3>
                        <p class="text-sm text-gray-500 mt-2" x-text="selectedTicket?.status_label"></p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600" @click="closeDetails">
                        <span class="sr-only">Fechar</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Solicitante</p>
                            <p class="font-medium text-gray-900" x-text="selectedTicket?.requester ?? 'Não informado'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Responsável</p>
                            <p class="font-medium text-gray-900" x-text="selectedTicket?.assignee ?? 'Não atribuído'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Prioridade</p>
                            <p class="font-medium text-gray-900" x-text="selectedTicket?.priority_label"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Criado em</p>
                            <p class="font-medium text-gray-900" x-text="selectedTicket?.created_at ?? 'Não informado'"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Descrição</p>
                        <div class="prose prose-sm max-w-none text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-4 whitespace-pre-line"
                            x-html="selectedTicket?.description_html || '<p class=\'text-gray-400\'>Descrição não disponível.</p>'">
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-200 flex justify-between items-center">
                    <a class="text-sm text-primary-600 hover:text-primary-800 font-medium" :href="selectedTicket?.show_url" target="_blank" rel="noopener">
                        Abrir chamado completo
                    </a>
                    <div class="flex gap-2">
                        <button class="btn btn-secondary" @click="closeDetails">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

                <!-- Finalização -->
        <div x-show="modals.finalize" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full">
                <div class="px-6 py-5 border-b border-gray-200 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-primary-600 uppercase tracking-wide truncate" x-text="finalizeTicket?.code"></p>
                        <h3 class="text-2xl font-semibold text-gray-900 mt-2">Finalizar chamado</h3>
                        <p class="text-sm text-gray-500 mt-2">Informe um resumo do que foi realizado antes de finalizar o chamado.</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 flex-shrink-0" @click="cancelFinalize">
                        <span class="sr-only">Cancelar</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-3">
                    <label for="finalize_summary" class="block text-sm font-medium text-gray-700">Resumo da resolução</label>
                    <textarea
                        id="finalize_summary"
                        x-model="finalizeSummary"
                        rows="6"
                        class="form-textarea w-full text-base leading-relaxed"
                        placeholder="Descreva como o chamado foi resolvido (mínimo 10 caracteres)."
                    ></textarea>
                    <p class="text-xs text-gray-500">
                        Esse texto será registrado no histórico do chamado.
                    </p>
                </div>
                <div class="px-6 py-5 border-t border-gray-200 flex justify-end gap-3">
                    <button class="btn btn-secondary" @click="cancelFinalize">Cancelar</button>
                    <button class="btn btn-primary" :disabled="isFinalizing" @click="submitFinalize">
                        <span x-show="!isFinalizing">Finalizar</span>
                        <span x-show="isFinalizing">Finalizando...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Retornar para fila -->
        <div x-show="modals.return" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full">
                <div class="px-6 py-5 border-b border-gray-200 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-primary-600 uppercase tracking-wide truncate" x-text="returnTicket?.code"></p>
                        <h3 class="text-2xl font-semibold text-gray-900 mt-2">Devolver para a fila</h3>
                        <p class="text-sm text-gray-500 mt-2">Informe o motivo para que possamos registrar a devolução do chamado para a fila.</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 flex-shrink-0" @click="cancelReturn">
                        <span class="sr-only">Cancelar</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Motivo da devolução</label>
                    <textarea
                        x-model="returnReason"
                        rows="5"
                        class="form-textarea w-full text-base leading-relaxed"
                        placeholder="Descreva o motivo da devolução (mínimo 5 caracteres)."
                    ></textarea>
                    <p class="text-xs text-gray-500">
                        O motivo será registrado no histórico do chamado.
                    </p>
                </div>
                <div class="px-6 py-5 border-t border-gray-200 flex justify-end gap-3">
                    <button class="btn btn-secondary" @click="cancelReturn">Cancelar</button>
                    <button class="btn btn-primary" :disabled="isReturning" @click="submitReturn">
                        <span x-show="!isReturning">Confirmar devolução</span>
                        <span x-show="isReturning">Processando...</span>
                    </button>
                </div>
            </div>
        </div>
<!-- Modal de Advertência -->
        <div x-show="modals.warning" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
            <div class="bg-white rounded-xl shadow-xl max-w-xs w-full mx-4">
                <div class="p-4 border-b border-gray-200 flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900">Atenção</h3>
                        <p class="text-xs text-gray-600 mt-1" x-text="warningMessage"></p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 flex-shrink-0" @click="closeWarning">
                        <span class="sr-only">Fechar</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4 flex justify-end">
                    <button class="btn btn-primary btn-sm" @click="closeWarning">Entendi</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function kanbanBoard(config) {
    return {
        columns: config.columns || [],
        tickets: config.tickets || [],
        endpoints: config.endpoints,
        csrf: config.csrf,
        sortables: [],
        renderKey: 0,
        flash: {
            message: null,
            timeout: null,
        },
        loadingTicket: null,
        modals: {
            details: false,
            finalize: false,
            return: false,
            warning: false,
        },
        warningMessage: '',
        selectedTicket: null,
        finalizeTicket: null,
        finalizeSummary: '',
        isFinalizing: false,
        returnTicket: null,
        returnReason: '',
        isReturning: false,
        pendingAction: null,

        init() {
            this.$nextTick(() => this.setupSortable());
        },

        setupSortable() {
            this.destroySortables();

            this.$nextTick(() => {
                document.querySelectorAll('[data-kanban-column]').forEach((columnEl) => {
                    const targetColumn = columnEl.dataset.status;
                    const dropzone = columnEl.querySelector('[data-kanban-dropzone]');

                    if (!dropzone) return;

                    // Variável para armazenar a última posição Y do mouse durante o drag
                    let lastMouseY = null;
                    
                    // Listener global para capturar a posição do mouse durante o drag
                    const handleDragOver = (e) => {
                        if (document.querySelector('.sortable-drag') || document.querySelector('.sortable-ghost')) {
                            lastMouseY = e.clientY;
                        }
                    };
                    
                    document.addEventListener('dragover', handleDragOver);
                    
                    const sortable = Sortable.create(dropzone, {
                        group: 'tickets-board',
                        animation: 200,
                        handle: '.kanban-card',
                        draggable: '.kanban-card',
                        multiDrag: false,
                        preventOnFilter: false,
                        ghostClass: 'sortable-ghost',
                        dragClass: 'sortable-drag',
                        chosenClass: 'sortable-chosen',
                        fallbackOnBody: true,
                        swapThreshold: 0.65,
                        forceFallback: false,
                        emptyInsertThreshold: 10000,
                        direction: 'vertical',
                        scroll: true,
                        scrollSensitivity: 100,
                        scrollSpeed: 20,
                        bubbleScroll: true,
                        onMove: (evt, originalEvent) => {
                            // Sempre permitir movimento se o cursor está dentro da dropzone
                            if (originalEvent) {
                                const dropzoneRect = dropzone.getBoundingClientRect();
                                const y = originalEvent.clientY;
                                const x = originalEvent.clientX;
                                
                                return (
                                    y >= dropzoneRect.top && 
                                    y <= dropzoneRect.bottom &&
                                    x >= dropzoneRect.left && 
                                    x <= dropzoneRect.right
                                );
                            }
                            return true;
                        },
                        onAdd: (evt) => {
                            // Reordenar baseado na posição do mouse se disponível
                            if (lastMouseY && evt.from !== evt.to) {
                                const dropzone = evt.to;
                                const item = evt.item;
                                const dropzoneRect = dropzone.getBoundingClientRect();
                                const relativeY = lastMouseY - dropzoneRect.top;
                                
                                const children = Array.from(dropzone.children)
                                    .filter(el => el !== item && el.classList.contains('kanban-card'));
                                
                                if (children.length > 0) {
                                    // Encontrar a posição correta baseado na altura
                                    let insertBefore = null;
                                    for (let child of children) {
                                        const childRect = child.getBoundingClientRect();
                                        const childRelativeY = childRect.top - dropzoneRect.top + (childRect.height / 2);
                                        
                                        if (relativeY < childRelativeY) {
                                            insertBefore = child;
                                            break;
                                        }
                                    }
                                    
                                    if (insertBefore) {
                                        dropzone.insertBefore(item, insertBefore);
                                    } else {
                                        dropzone.appendChild(item);
                                    }
                                }
                            }
                        },
                        onEnd: (event) => {
                            const ticketId = Number(event.item.dataset.ticketId);
                            const fromColumn = event.from.closest('[data-kanban-column]')?.dataset.status;
                            const toColumn = event.to.closest('[data-kanban-column]')?.dataset.status || columnEl.dataset.status;

                            if (!ticketId || !fromColumn || !toColumn) {
                                this.forceRerender();
                                return;
                            }

                            // Não fazer nada se já está na mesma coluna
                            if (toColumn === fromColumn) {
                                this.forceRerender();
                                return;
                            }

                            const ticket = this.findTicket(ticketId);
                            if (!ticket) {
                                this.forceRerender();
                                return;
                            }

                            if (toColumn === 'finalized') {
                                this.pendingAction = { ticket, fromColumn, toColumn: 'finalized' };
                                this.openFinalize(ticket);
                                this.forceRerender();
                                return;
                            }

                            if (toColumn === 'queue') {
                                this.pendingAction = { ticket, fromColumn, toColumn: 'queue' };
                                this.openReturn(ticket);
                                this.forceRerender();
                                return;
                            }

                            if (toColumn === 'in_progress' || toColumn === 'waiting_user') {
                                this.updateTicketColumn(ticket, toColumn);
                                return;
                            }

                            this.forceRerender();
                        },
                    });

                    this.sortables.push(sortable);
                });
            });
        },

        destroySortables() {
            this.sortables.forEach(sortable => sortable.destroy());
            this.sortables = [];
        },

        forceRerender() {
            this.renderKey += 1;
            this.$nextTick(() => this.setupSortable());
        },

        findTicket(id) {
            return this.tickets.find(ticket => Number(ticket.id) === Number(id));
        },

        getTickets(columnKey) {
            return this.tickets
                .filter(ticket => ticket.column === columnKey)
                .sort((a, b) => (b.updated_ts || 0) - (a.updated_ts || 0));
        },

        openDetails(ticket) {
            this.selectedTicket = ticket;
            this.modals.details = true;
        },

        closeDetails() {
            this.modals.details = false;
            this.selectedTicket = null;
        },

        openFinalize(ticket) {
            this.finalizeTicket = ticket;
            this.finalizeSummary = '';
            this.modals.finalize = true;
        },

        cancelFinalize() {
            this.modals.finalize = false;
            this.finalizeTicket = null;
            this.finalizeSummary = '';
            this.pendingAction = null;
            this.forceRerender();
        },

        openReturn(ticket) {
            this.returnTicket = ticket;
            this.returnReason = '';
            this.modals.return = true;
        },

        cancelReturn() {
            this.modals.return = false;
            this.returnTicket = null;
            this.returnReason = '';
            this.pendingAction = null;
            this.forceRerender();
        },

        async submitFinalize() {
            if (!this.finalizeTicket || this.finalizeSummary.trim().length < 10) {
                this.showWarning('Informe um resumo com pelo menos 10 caracteres.');
                return;
            }

            this.isFinalizing = true;
            
            try {
                await this.updateTicketColumn(this.finalizeTicket, 'finalized', {
                    resolution_summary: this.finalizeSummary,
                });
                
                this.modals.finalize = false;
                this.finalizeTicket = null;
                this.finalizeSummary = '';
            } catch (error) {
                console.error('Erro ao finalizar:', error);
                this.showFlash(error.message || 'Erro ao finalizar o chamado.', true);
            } finally {
                this.isFinalizing = false;
            }
        },

        async submitReturn() {
            if (!this.returnTicket || this.returnReason.trim().length < 5) {
                this.showWarning('Informe o motivo com pelo menos 5 caracteres.');
                return;
            }

            this.isReturning = true;
            await this.updateTicketColumn(this.returnTicket, 'queue', {
                return_reason: this.returnReason,
            });
            this.isReturning = false;

            this.modals.return = false;
            this.returnTicket = null;
            this.returnReason = '';
        },

        async updateTicketColumn(ticket, targetColumn, extraPayload = {}) {
            const previousColumn = ticket.column;
            const previousStatus = ticket.status;
            const previousStatusLabel = ticket.status_label;
            const previousAssignee = ticket.assignee;
            const previousAssigneeId = ticket.assignee_id;
            const previousClass = ticket.card_class;

            this.loadingTicket = ticket.id;

            try {
                const response = await fetch(this.endpoints.update.replace('__TICKET__', ticket.id), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        column: targetColumn,
                        ...extraPayload,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Não foi possível atualizar o chamado.');
                }

                // ATUALIZAR TODOS OS CAMPOS DO TICKET COM OS DADOS DO SERVIDOR
                Object.assign(ticket, {
                    status: data.ticket.status,
                    status_label: data.ticket.status_label,
                    assignee: data.ticket.assignee,
                    assignee_id: data.ticket.assignee_id,
                    updated_human: data.ticket.updated_human,
                    updated_ts: data.ticket.updated_ts,
                    card_class: data.ticket.card_class,
                    column: data.ticket.column,
                });

                // FORÇAR ATUALIZAÇÃO VISUAL
                this.forceRerender();

                this.showFlash(data.message || 'Chamado atualizado com sucesso.');
            } catch (error) {
                ticket.column = previousColumn;
                ticket.status = previousStatus;
                ticket.status_label = previousStatusLabel;
                ticket.assignee = previousAssignee;
                ticket.assignee_id = previousAssigneeId;
                ticket.card_class = previousClass;
                this.showFlash(error.message, true);
            } finally {
                this.loadingTicket = null;
                this.pendingAction = null;
                this.forceRerender();
            }
        },

        showFlash(message, isError = false) {
            if (this.flash.timeout) {
                clearTimeout(this.flash.timeout);
            }

            this.flash.message = message;

            if (!isError) {
                this.flash.timeout = setTimeout(() => {
                    this.flash.message = null;
                    this.flash.timeout = null;
                }, 4000);
            }
        },

        showWarning(message) {
            this.warningMessage = message;
            this.modals.warning = true;
        },

        closeWarning() {
            this.modals.warning = false;
            this.warningMessage = '';
        },
    };
}
</script>
@endpush
