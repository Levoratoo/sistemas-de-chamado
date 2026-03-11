import Chart from 'chart.js/auto';
import {
    failNextDemoRequest,
    getAreaColorClass,
    getAttendantPerformanceChart,
    getCurrentDemoUser,
    getDashboardSummary,
    getDemoAreas,
    getDemoRequestTypes,
    getKanbanBoard,
    getRecentNotifications,
    getRequestTypeClass,
    getSlaComplianceChart,
    getStatusDistributionChart,
    getTrendChart,
    getAreaDistributionChart,
    getUnreadCount,
    listTickets,
    markAllNotificationsAsRead,
    markNotificationAsRead,
    updateKanbanStatus,
    type KanbanUpdatePayload,
    type TicketColumn,
    type TicketListQuery,
    type TicketListResponse,
} from '../resources/js/lib/portfolio-demo';

type ViewId = 'dashboard' | 'tickets' | 'kanban';
type SortableInstance = { destroy: () => void };

declare global {
    interface Window {
        Sortable?: new (element: HTMLElement, options: Record<string, unknown>) => SortableInstance;
        __portfolioFailNextRequest?: () => void;
    }
}

const chartStore = new Map<string, Chart>();
const sortables: SortableInstance[] = [];

const state = {
    currentView: 'dashboard' as ViewId,
    ticketsQuery: {
        page: 1,
        per_page: 12,
        sort_by: 'created_at',
        sort_dir: 'desc',
        search: '',
        status: '',
        priority: '',
        request_type: '',
        area_id: '',
        assigned_to_me: false,
        my_tickets: false,
    } satisfies TicketListQuery,
    ticketsPagination: {
        current_page: 1,
        last_page: 1,
        per_page: 12,
        total: 0,
    },
    kanbanFilters: {
        search: '',
        assigned_to_me: false,
        my_tickets: false,
    },
    pendingKanbanAction: null as null | {
        ticketId: number;
        targetColumn: TicketColumn;
    },
};

const views: ViewId[] = ['dashboard', 'tickets', 'kanban'];
const kanbanColumnsOrder: TicketColumn[] = ['queue', 'in_progress', 'waiting_user', 'finalized'];

document.addEventListener('DOMContentLoaded', () => {
    bindNavigation();
    bindNotifications();
    bindTickets();
    bindKanban();
    hydrateStaticData();
    setActiveView('dashboard');

    void refreshNotificationCount();
    void loadDashboard();
    void loadTickets();
    void loadKanban();

    window.setInterval(() => {
        void refreshNotificationCount();
    }, 30000);
});

function hydrateStaticData(): void {
    const currentUser = getCurrentDemoUser();
    setText('current-user-name', currentUser.name);

    const requestTypeSelect = byId<HTMLSelectElement>('filter-request-type');
    getDemoRequestTypes().forEach((type) => {
        const option = document.createElement('option');
        option.value = type.value;
        option.textContent = type.label;
        requestTypeSelect.appendChild(option);
    });

    const areaSelect = byId<HTMLSelectElement>('filter-area');
    getDemoAreas().forEach((area) => {
        const option = document.createElement('option');
        option.value = String(area.id);
        option.textContent = area.name;
        areaSelect.appendChild(option);
    });
}

function bindNavigation(): void {
    const nav = byId<HTMLDivElement>('main-nav');
    nav.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        const button = target.closest<HTMLButtonElement>('button[data-view]');
        if (!button) {
            return;
        }

        const view = button.dataset.view as ViewId;
        if (!views.includes(view)) {
            return;
        }

        setActiveView(view);
    });
}

function setActiveView(view: ViewId): void {
    state.currentView = view;

    views.forEach((value) => {
        const section = byId<HTMLElement>(`view-${value}`);
        section.classList.toggle('view-hidden', value !== view);
    });

    const buttons = Array.from(document.querySelectorAll<HTMLButtonElement>('#main-nav button[data-view]'));
    buttons.forEach((button) => {
        const active = button.dataset.view === view;
        button.classList.toggle('bg-primary-600', active);
        button.classList.toggle('text-white', active);
        button.classList.toggle('text-gray-700', !active);
        button.classList.toggle('hover:bg-gray-100', !active);
    });
}

async function loadDashboard(): Promise<void> {
    try {
        const summary = await getDashboardSummary();
        setText('metric-total', String(summary.totalTickets));
        setText('metric-this-month', `+${summary.ticketsThisMonth} este mes`);
        setText('metric-sla', `${summary.slaOnTime}%`);
        setText('metric-overdue', summary.overdueTickets > 0 ? `${summary.overdueTickets} vencidos` : 'Todos no prazo');
        setText('metric-avg-resolution', summary.avgResolutionTime);
        setText('metric-satisfaction', `${summary.satisfactionRate}%`);
        setText('metric-evaluations', `${summary.totalEvaluations} avaliacoes`);
    } catch (error) {
        showFlash(getErrorMessage(error, 'Nao foi possivel carregar os indicadores.'), true);
    }

    await Promise.all([
        drawTrendChart(),
        drawAreaDistributionChart(),
        drawSlaChart(),
        drawAttendantChart(),
        drawStatusChart(),
    ]);
}

async function drawTrendChart(): Promise<void> {
    await drawChart('trendChart', 'trendChartState', async () => {
        return {
            type: 'line' as const,
            data: await getTrendChart(),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' as const },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            },
        };
    });
}

async function drawAreaDistributionChart(): Promise<void> {
    await drawChart('areaDistributionChart', 'areaDistributionChartState', async () => {
        return {
            type: 'doughnut' as const,
            data: await getAreaDistributionChart(),
            options: { responsive: true, maintainAspectRatio: false },
        };
    });
}

async function drawSlaChart(): Promise<void> {
    await drawChart('slaComplianceChart', 'slaComplianceChartState', async () => {
        const response = await getSlaComplianceChart();
        return {
            type: 'doughnut' as const,
            data: {
                labels: ['Em dia', 'Vencidos'],
                datasets: [{ data: [response.onTime, response.overdue], backgroundColor: ['rgb(34, 197, 94)', 'rgb(239, 68, 68)'] }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom' as const },
                    tooltip: { enabled: true },
                },
            },
            centerText: `${response.complianceRate}%`,
        };
    });
}

async function drawAttendantChart(): Promise<void> {
    await drawChart('attendantPerformanceChart', 'attendantPerformanceChartState', async () => {
        return {
            type: 'bar' as const,
            data: await getAttendantPerformanceChart(),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { ticks: { maxRotation: 45, minRotation: 45 } },
                },
            },
        };
    });
}

async function drawStatusChart(): Promise<void> {
    await drawChart('statusDistributionChart', 'statusDistributionChartState', async () => {
        return {
            type: 'pie' as const,
            data: await getStatusDistributionChart(),
            options: { responsive: true, maintainAspectRatio: false },
        };
    });
}

async function drawChart(
    canvasId: string,
    stateId: string,
    loader: () => Promise<{
        type: 'line' | 'bar' | 'doughnut' | 'pie';
        data: any;
        options: any;
        centerText?: string;
    }>,
): Promise<void> {
    setChartState(stateId, 'Carregando...', false);

    try {
        const chartConfig = await loader();
        const canvas = byId<HTMLCanvasElement>(canvasId);
        const existing = chartStore.get(canvasId);
        if (existing) {
            existing.destroy();
        }

        const chart = new Chart(canvas, {
            type: chartConfig.type,
            data: chartConfig.data,
            options: chartConfig.options,
            plugins: chartConfig.centerText
                ? [{
                    id: 'centerText',
                    afterDraw(instance) {
                        const { ctx, chartArea } = instance;
                        const x = (chartArea.left + chartArea.right) / 2;
                        const y = (chartArea.top + chartArea.bottom) / 2;
                        ctx.save();
                        ctx.font = '700 22px Figtree';
                        ctx.fillStyle = '#334155';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(chartConfig.centerText ?? '', x, y);
                        ctx.restore();
                    },
                }]
                : [],
        });

        chartStore.set(canvasId, chart);
        setChartState(stateId, '', false, true);
    } catch (error) {
        setChartState(stateId, getErrorMessage(error, 'Erro ao carregar grafico.'), true);
    }
}

function setChartState(stateId: string, message: string, isError: boolean, hidden = false): void {
    const stateEl = byId<HTMLDivElement>(stateId);
    stateEl.classList.toggle('hidden', hidden);
    stateEl.textContent = message;
    stateEl.classList.toggle('text-red-600', isError);
    stateEl.classList.toggle('text-gray-500', !isError);
}

function bindTickets(): void {
    const form = byId<HTMLFormElement>('tickets-filters');
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        state.ticketsQuery = {
            ...state.ticketsQuery,
            page: 1,
            status: formValue('filter-status'),
            request_type: formValue('filter-request-type'),
            priority: formValue('filter-priority'),
            area_id: formValue('filter-area'),
            search: formValue('filter-search'),
            assigned_to_me: byId<HTMLInputElement>('filter-assigned').checked,
            my_tickets: byId<HTMLInputElement>('filter-mine').checked,
        };
        void loadTickets();
    });

    byId<HTMLButtonElement>('tickets-clear').addEventListener('click', () => {
        form.reset();
        state.ticketsQuery = {
            ...state.ticketsQuery,
            page: 1,
            status: '',
            request_type: '',
            priority: '',
            area_id: '',
            search: '',
            assigned_to_me: false,
            my_tickets: false,
        };
        void loadTickets();
    });

    byId<HTMLButtonElement>('tickets-prev').addEventListener('click', () => {
        if (state.ticketsPagination.current_page <= 1) {
            return;
        }
        state.ticketsQuery.page = state.ticketsPagination.current_page - 1;
        void loadTickets();
    });

    byId<HTMLButtonElement>('tickets-next').addEventListener('click', () => {
        if (state.ticketsPagination.current_page >= state.ticketsPagination.last_page) {
            return;
        }
        state.ticketsQuery.page = state.ticketsPagination.current_page + 1;
        void loadTickets();
    });

    const sortableHeaders = Array.from(document.querySelectorAll<HTMLTableHeaderCellElement>('th[data-sort]'));
    const allowedSorts = new Set(['code', 'title', 'created_at', 'updated_at', 'priority', 'status']);

    sortableHeaders.forEach((header) => {
        header.addEventListener('click', () => {
            const sortBy = header.dataset.sort ?? '';
            if (!allowedSorts.has(sortBy)) {
                return;
            }

            if (state.ticketsQuery.sort_by === sortBy) {
                state.ticketsQuery.sort_dir = state.ticketsQuery.sort_dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.ticketsQuery.sort_by = sortBy as TicketListQuery['sort_by'];
                state.ticketsQuery.sort_dir = 'asc';
            }

            state.ticketsQuery.page = 1;
            void loadTickets();
        });
    });
}

async function loadTickets(): Promise<void> {
    const body = byId<HTMLTableSectionElement>('tickets-table-body');
    body.innerHTML = '<tr><td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">Carregando chamados...</td></tr>';

    try {
        const response = await listTickets(state.ticketsQuery);
        state.ticketsPagination = response.pagination;
        renderTickets(response);
    } catch (error) {
        body.innerHTML = `<tr><td colspan="9" class="px-4 py-6 text-center text-sm text-red-600">${getErrorMessage(error, 'Erro ao carregar chamados.')}</td></tr>`;
    }
}

function renderTickets(response: TicketListResponse): void {
    const body = byId<HTMLTableSectionElement>('tickets-table-body');
    if (!response.data.length) {
        body.innerHTML = '<tr><td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">Nenhum chamado encontrado.</td></tr>';
    } else {
        body.innerHTML = response.data.map((ticket) => {
            const requestTypeClass = getRequestTypeClass(ticket.request_type);
            const areaClass = getAreaColorClass(ticket.area.name);
            const slaClass = ticket.sla_status === 'overdue' ? 'badge-danger' : ticket.sla_status === 'warning' ? 'badge-warning' : 'badge-success';

            return `
                <tr class="hover:bg-gray-50 align-top">
                    <td class="px-4 py-4 text-sm font-semibold text-primary-600">${ticket.code}</td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${requestTypeClass}">${ticket.request_type_label}</span>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-900 max-w-xs truncate">${ticket.title}</td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${areaClass}">${ticket.area.name}</span>
                    </td>
                    <td class="px-4 py-4"><span class="badge ${ticket.status_badge_class}">${ticket.status_label}</span></td>
                    <td class="px-4 py-4"><span class="badge ${ticket.priority_badge_class}">${ticket.priority_label}</span></td>
                    <td class="px-4 py-4"><span class="badge ${slaClass}">${ticket.sla_label}</span></td>
                    <td class="px-4 py-4 text-sm text-gray-600">${ticket.assignee?.name ?? '-'}</td>
                    <td class="px-4 py-4 text-sm text-gray-500">${ticket.updated_human}</td>
                </tr>
            `;
        }).join('');
    }

    const { current_page, last_page, total } = response.pagination;
    setText('tickets-pagination-info', `Pagina ${current_page} de ${last_page} · ${total} chamados`);
    byId<HTMLButtonElement>('tickets-prev').disabled = current_page <= 1;
    byId<HTMLButtonElement>('tickets-next').disabled = current_page >= last_page;
}

function bindKanban(): void {
    const form = byId<HTMLFormElement>('kanban-filters');
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        state.kanbanFilters.search = formValue('kanban-search');
        state.kanbanFilters.assigned_to_me = byId<HTMLInputElement>('kanban-assigned').checked;
        state.kanbanFilters.my_tickets = byId<HTMLInputElement>('kanban-mine').checked;
        void loadKanban();
    });

    byId<HTMLButtonElement>('kanban-clear').addEventListener('click', () => {
        form.reset();
        state.kanbanFilters = { search: '', assigned_to_me: false, my_tickets: false };
        void loadKanban();
    });

    byId<HTMLButtonElement>('kanban-action-cancel').addEventListener('click', () => {
        closeKanbanModal();
        void loadKanban();
    });

    byId<HTMLButtonElement>('kanban-action-confirm').addEventListener('click', () => {
        void confirmKanbanAction();
    });
}

async function loadKanban(): Promise<void> {
    setText('kanban-state', 'Carregando quadro...');

    try {
        const board = await getKanbanBoard(state.kanbanFilters);
        renderKanban(board.columnsMeta, board.tickets);
        setText('kanban-state', `${board.tickets.length} chamados visiveis no quadro.`);
    } catch (error) {
        setText('kanban-state', getErrorMessage(error, 'Erro ao carregar Kanban.'));
    }
}

function renderKanban(columnsMeta: Record<TicketColumn, { label: string; description: string; bg: string; accent: string }>, tickets: any[]): void {
    const board = byId<HTMLDivElement>('kanban-board');
    board.innerHTML = kanbanColumnsOrder.map((columnKey) => {
        const column = columnsMeta[columnKey];
        const columnTickets = tickets.filter((ticket) => ticket.column === columnKey);

        return `
            <section class="kanban-column ${column.bg}" data-status="${columnKey}">
                <header class="kanban-column__header">
                    <div class="h-2 rounded-full mb-3 ${column.accent}"></div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">${column.label}</h3>
                        <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-sm font-bold text-gray-700 bg-gray-100">${columnTickets.length}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">${column.description}</p>
                </header>
                <div class="kanban-column__body" data-dropzone data-status="${columnKey}">
                    ${columnTickets.map((ticket) => `
                        <article class="kanban-card ${ticket.card_class}" data-ticket-id="${ticket.id}">
                            <div class="p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-primary-600">${ticket.code}</span>
                                    <span class="badge ${ticket.priority_badge}">${ticket.priority_label}</span>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900 line-clamp-2">${ticket.title}</h4>
                                <div class="space-y-1 text-xs text-gray-500">
                                    <p><span class="text-gray-400">Status:</span> ${ticket.status_label}</p>
                                    <p><span class="text-gray-400">Solicitante:</span> ${ticket.requester}</p>
                                    <p><span class="text-gray-400">Responsavel:</span> ${ticket.assignee ?? 'Nao atribuido'}</p>
                                    <p><span class="text-gray-400">Atualizado:</span> ${ticket.updated_human}</p>
                                </div>
                            </div>
                        </article>
                    `).join('')}
                </div>
            </section>
        `;
    }).join('');

    sortables.forEach((sortable) => sortable.destroy());
    sortables.length = 0;

    const dropzones = Array.from(board.querySelectorAll<HTMLDivElement>('[data-dropzone]'));
    const SortableCtor = window.Sortable;

    if (!SortableCtor) {
        setText('kanban-state', 'Erro: Sortable.js nao carregado.');
        return;
    }

    dropzones.forEach((dropzone) => {
        const sortable = new SortableCtor(dropzone, {
            group: 'portfolio-kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: (event) => {
                const item = event.item as HTMLElement;
                const ticketId = Number(item.dataset.ticketId);
                const fromColumn = (event.from as HTMLElement).dataset.status as TicketColumn;
                const toColumn = (event.to as HTMLElement).dataset.status as TicketColumn;

                if (!ticketId || !toColumn || fromColumn === toColumn) {
                    void loadKanban();
                    return;
                }

                void submitKanbanUpdate(ticketId, toColumn);
            },
        });

        sortables.push(sortable);
    });
}

async function submitKanbanUpdate(ticketId: number, targetColumn: TicketColumn): Promise<void> {
    if (targetColumn === 'finalized' || targetColumn === 'queue') {
        state.pendingKanbanAction = { ticketId, targetColumn };
        openKanbanModal(targetColumn);
        return;
    }

    await runKanbanUpdate(ticketId, { column: targetColumn });
}

function openKanbanModal(targetColumn: TicketColumn): void {
    const title = targetColumn === 'finalized' ? 'Finalizar chamado' : 'Devolver chamado para fila';
    const help = targetColumn === 'finalized'
        ? 'Informe um resumo da resolucao (minimo 10 caracteres).'
        : 'Informe o motivo da devolucao (minimo 5 caracteres).';

    setText('kanban-action-title', title);
    setText('kanban-action-help', help);
    byId<HTMLTextAreaElement>('kanban-action-input').value = '';

    const modal = byId<HTMLDivElement>('kanban-action-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeKanbanModal(): void {
    state.pendingKanbanAction = null;
    const modal = byId<HTMLDivElement>('kanban-action-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function confirmKanbanAction(): Promise<void> {
    if (!state.pendingKanbanAction) {
        return;
    }

    const input = byId<HTMLTextAreaElement>('kanban-action-input').value.trim();
    const { ticketId, targetColumn } = state.pendingKanbanAction;

    const payload: KanbanUpdatePayload = {
        column: targetColumn,
    };

    if (targetColumn === 'finalized') {
        payload.resolution_summary = input;
    } else if (targetColumn === 'queue') {
        payload.return_reason = input;
    }

    await runKanbanUpdate(ticketId, payload);
    closeKanbanModal();
}

async function runKanbanUpdate(ticketId: number, payload: KanbanUpdatePayload): Promise<void> {
    setText('kanban-state', 'Atualizando chamado...');

    try {
        const response = await updateKanbanStatus(ticketId, payload);
        showFlash(response.message);
        await Promise.all([loadKanban(), loadTickets(), loadDashboard(), refreshNotificationCount()]);
    } catch (error) {
        showFlash(getErrorMessage(error, 'Nao foi possivel atualizar o chamado.'), true);
        await loadKanban();
    }
}

function bindNotifications(): void {
    const toggle = byId<HTMLButtonElement>('notifications-toggle');
    const dropdown = byId<HTMLDivElement>('notifications-dropdown');

    toggle.addEventListener('click', () => {
        const isHidden = dropdown.classList.contains('hidden');
        dropdown.classList.toggle('hidden');
        if (isHidden) {
            void loadNotifications();
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        if (!target.closest('#notifications-toggle') && !target.closest('#notifications-dropdown')) {
            dropdown.classList.add('hidden');
        }
    });

    byId<HTMLButtonElement>('mark-all-notifications').addEventListener('click', async () => {
        try {
            await markAllNotificationsAsRead();
            await Promise.all([loadNotifications(), refreshNotificationCount()]);
            showFlash('Todas as notificacoes foram marcadas como lidas.');
        } catch (error) {
            showFlash(getErrorMessage(error, 'Falha ao marcar notificacoes.'), true);
        }
    });
}

async function refreshNotificationCount(): Promise<void> {
    try {
        const { count } = await getUnreadCount();
        const badge = byId<HTMLSpanElement>('notification-badge');
        if (count > 0) {
            badge.textContent = String(count);
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }
    } catch {
        // Silencioso para nao poluir a UI.
    }
}

async function loadNotifications(): Promise<void> {
    const list = byId<HTMLDivElement>('notifications-list');
    list.innerHTML = '<div class="p-4 text-center text-gray-500">Carregando notificacoes...</div>';

    try {
        const response = await getRecentNotifications();
        if (!response.notifications.length) {
            list.innerHTML = '<div class="p-4 text-center text-gray-500">Nenhuma notificacao.</div>';
            return;
        }

        list.innerHTML = response.notifications.map((notification) => {
            const unread = !notification.read_at;
            const message = escapeHtml(notification.data.message);
            const createdAt = formatDateTime(notification.created_at);

            return `
                <article class="p-4 border-b border-gray-100 ${unread ? 'bg-white' : 'bg-gray-50'}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm text-gray-900">${message}</p>
                            <p class="text-xs text-gray-500 mt-1">${createdAt}</p>
                        </div>
                        ${unread ? `<button data-read-id="${notification.id}" class="text-xs text-primary-600 hover:text-primary-800">Marcar</button>` : ''}
                    </div>
                </article>
            `;
        }).join('');

        Array.from(list.querySelectorAll<HTMLButtonElement>('button[data-read-id]')).forEach((button) => {
            button.addEventListener('click', async () => {
                const id = button.dataset.readId;
                if (!id) {
                    return;
                }

                try {
                    await markNotificationAsRead(id);
                    await Promise.all([loadNotifications(), refreshNotificationCount()]);
                } catch (error) {
                    showFlash(getErrorMessage(error, 'Nao foi possivel marcar como lida.'), true);
                }
            });
        });
    } catch (error) {
        list.innerHTML = `<div class="p-4 text-center text-red-600">${getErrorMessage(error, 'Erro ao carregar notificacoes.')}</div>`;
    }
}

function showFlash(message: string, isError = false): void {
    const flash = byId<HTMLDivElement>('flash-message');
    flash.textContent = message;
    flash.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'border', 'border-green-200', 'bg-red-100', 'text-red-800', 'border-red-200');

    if (isError) {
        flash.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-200');
    } else {
        flash.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-200');
    }

    window.setTimeout(() => {
        flash.classList.add('hidden');
    }, 4000);
}

function getErrorMessage(error: unknown, fallback: string): string {
    if (error && typeof error === 'object' && 'message' in error && typeof (error as any).message === 'string') {
        return (error as any).message;
    }
    return fallback;
}

function formatDateTime(value: string): string {
    const date = new Date(value);
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function setText(id: string, text: string): void {
    byId<HTMLElement>(id).textContent = text;
}

function formValue(id: string): string {
    return byId<HTMLInputElement | HTMLSelectElement>(id).value;
}

function byId<T extends HTMLElement>(id: string): T {
    const element = document.getElementById(id);
    if (!element) {
        throw new Error(`Elemento nao encontrado: ${id}`);
    }
    return element as T;
}

function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Atalho util para demonstrar estados de erro manualmente via console.
window.__portfolioFailNextRequest = () => failNextDemoRequest();
