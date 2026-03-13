import '../resources/css/app.css';
import Chart from 'chart.js/auto';
import {
    createTicket,
    failNextDemoRequest,
    getAreaColorClass,
    getAttendantPerformanceChart,
    getCurrentDemoUser,
    getDashboardSummary,
    getDemoAreas,
    getDemoRequestTypes,
    getTicketDetails,
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
    type TicketPriority,
} from '../resources/js/lib/portfolio-demo';

type ViewId = 'dashboard' | 'create' | 'tickets' | 'kanban';
type SortableInstance = { destroy: () => void };
type CreateTemplateId = 'suporte_ti' | 'reembolso' | 'compra';

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

const createExtraFieldMap: Record<string, Array<{ key: string; label: string; placeholder: string }>> = {
    suporte_ti: [
        { key: 'equipamento', label: 'Equipamento', placeholder: 'Ex.: Notebook Dell Latitude' },
        { key: 'setor', label: 'Setor', placeholder: 'Ex.: Comercial' },
        { key: 'ramal', label: 'Ramal', placeholder: 'Ex.: 214' },
    ],
    reembolso: [
        { key: 'valor', label: 'Valor', placeholder: 'Ex.: R$ 189,90' },
        { key: 'centro_custo', label: 'Centro de Custo', placeholder: 'Ex.: FIN-023' },
        { key: 'fornecedor', label: 'Fornecedor', placeholder: 'Ex.: Posto XYZ' },
    ],
    compra: [
        { key: 'item', label: 'Item', placeholder: 'Ex.: Bobina BOPP 30 micras' },
        { key: 'quantidade', label: 'Quantidade', placeholder: 'Ex.: 20 unidades' },
        { key: 'fornecedor', label: 'Fornecedor sugerido', placeholder: 'Ex.: Embala Sul' },
    ],
};

const createTemplates: Record<CreateTemplateId, {
    title: string;
    request_type: string;
    area_id: number;
    priority: TicketPriority;
    description: string;
    extra_fields: Record<string, string>;
}> = {
    suporte_ti: {
        title: 'Usuario sem acesso ao ERP no setor comercial',
        request_type: 'suporte_ti',
        area_id: 2,
        priority: 'high',
        description: 'Usuario reporta erro de login no ERP desde o inicio do expediente. Necessario normalizar o acesso para continuidade das atividades.',
        extra_fields: {
            equipamento: 'Notebook Dell Latitude 5430',
            setor: 'Comercial',
            ramal: '214',
        },
    },
    reembolso: {
        title: 'Reembolso de despesa de viagem comercial',
        request_type: 'reembolso',
        area_id: 1,
        priority: 'medium',
        description: 'Solicitacao de reembolso referente a deslocamento para visita tecnica em cliente. Documentacao fiscal anexada no fluxo real.',
        extra_fields: {
            valor: 'R$ 286,40',
            centro_custo: 'FIN-109',
            fornecedor: 'Rede Posto Brasil',
        },
    },
    compra: {
        title: 'Compra de insumos para reposicao de estoque',
        request_type: 'compra',
        area_id: 3,
        priority: 'medium',
        description: 'Necessaria aprovacao de pedido para reposicao de insumos criticos da linha de producao.',
        extra_fields: {
            item: 'Filme stretch industrial 500mm',
            quantidade: '30 rolos',
            fornecedor: 'Pack Solutions',
        },
    },
};

const views: ViewId[] = ['dashboard', 'create', 'tickets', 'kanban'];
const kanbanColumnsOrder: TicketColumn[] = ['queue', 'in_progress', 'waiting_user', 'finalized'];

document.addEventListener('DOMContentLoaded', () => {
    bindNavigation();
    bindNotifications();
    bindTicketDetailsModal();
    bindCreateTicket();
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

    const requestTypes = getDemoRequestTypes();
    const filterRequestTypeSelect = byId<HTMLSelectElement>('filter-request-type');
    const createRequestTypeSelect = byId<HTMLSelectElement>('create-request-type');
    requestTypes.forEach((type) => {
        const option = document.createElement('option');
        option.value = type.value;
        option.textContent = type.label;
        filterRequestTypeSelect.appendChild(option);
        createRequestTypeSelect.appendChild(option.cloneNode(true));
    });

    const filterAreaSelect = byId<HTMLSelectElement>('filter-area');
    const createAreaSelect = byId<HTMLSelectElement>('create-area');
    getDemoAreas().forEach((area) => {
        const option = document.createElement('option');
        option.value = String(area.id);
        option.textContent = area.name;
        filterAreaSelect.appendChild(option);
        createAreaSelect.appendChild(option.cloneNode(true));
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

function bindTicketDetailsModal(): void {
    const modal = byId<HTMLDivElement>('ticket-details-modal');

    byId<HTMLButtonElement>('ticket-details-close').addEventListener('click', () => {
        closeTicketDetailsModal();
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeTicketDetailsModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeTicketDetailsModal();
        }
    });
}

function closeTicketDetailsModal(): void {
    const modal = byId<HTMLDivElement>('ticket-details-modal');
    modal.dataset.ticketId = '';
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function openTicketDetails(ticketId: number): Promise<void> {
    const modal = byId<HTMLDivElement>('ticket-details-modal');
    const stateEl = byId<HTMLDivElement>('ticket-details-state');
    const content = byId<HTMLDivElement>('ticket-details-content');

    const activeToken = `${ticketId}-${Date.now()}`;
    modal.dataset.ticketId = activeToken;
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    stateEl.textContent = 'Carregando detalhes...';
    stateEl.classList.remove('hidden', 'text-red-600');
    stateEl.classList.add('text-gray-500');
    content.classList.add('hidden');

    try {
        const response = await getTicketDetails(ticketId);
        if (modal.dataset.ticketId !== activeToken) {
            return;
        }

        renderTicketDetails(response.ticket);
        stateEl.classList.add('hidden');
        content.classList.remove('hidden');
    } catch (error) {
        if (modal.dataset.ticketId !== activeToken) {
            return;
        }

        stateEl.textContent = getErrorMessage(error, 'Erro ao carregar detalhes do chamado.');
        stateEl.classList.remove('text-gray-500');
        stateEl.classList.add('text-red-600');
        content.classList.add('hidden');
    }
}

function renderTicketDetails(ticket: TicketListResponse['data'][number]): void {
    setText('ticket-details-code', ticket.code);
    setText('ticket-details-title', ticket.title);
    setText('ticket-details-area', ticket.area.name);
    setText('ticket-details-requester', ticket.requester.name);
    setText('ticket-details-assignee', ticket.assignee?.name ?? 'Nao atribuido');
    setText('ticket-details-created', formatDateTime(ticket.created_at));
    setText('ticket-details-updated', formatDateTime(ticket.updated_at));
    setText('ticket-details-due', formatDateTime(ticket.due_at));
    setText('ticket-details-description', ticket.description);

    const slaClass = ticket.sla_status === 'overdue'
        ? 'badge-danger'
        : ticket.sla_status === 'warning'
            ? 'badge-warning'
            : 'badge-success';

    setBadge('ticket-details-status', ticket.status_label, ticket.status_badge_class);
    setBadge('ticket-details-priority', ticket.priority_label, ticket.priority_badge_class);
    setBadge('ticket-details-sla', ticket.sla_label, slaClass);
    setPill('ticket-details-request-type', ticket.request_type_label, getRequestTypeClass(ticket.request_type));

    const resolvedRow = byId<HTMLDivElement>('ticket-details-resolved-row');
    const resolutionRow = byId<HTMLDivElement>('ticket-details-resolution-row');

    if (ticket.resolved_at) {
        setText('ticket-details-resolved', formatDateTime(ticket.resolved_at));
        resolvedRow.classList.remove('hidden');
    } else {
        resolvedRow.classList.add('hidden');
    }

    if (ticket.resolution_summary && ticket.resolution_summary.trim().length > 0) {
        setText('ticket-details-resolution', ticket.resolution_summary);
        resolutionRow.classList.remove('hidden');
    } else {
        resolutionRow.classList.add('hidden');
    }
}

function setBadge(id: string, text: string, badgeClass: string): void {
    const element = byId<HTMLSpanElement>(id);
    element.className = `badge ${badgeClass}`;
    element.textContent = text;
}

function setPill(id: string, text: string, classes: string): void {
    const element = byId<HTMLSpanElement>(id);
    element.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${classes}`;
    element.textContent = text;
}

function bindCreateTicket(): void {
    const form = byId<HTMLFormElement>('create-ticket-form');
    const requestTypeSelect = byId<HTMLSelectElement>('create-request-type');
    const templateButtons = Array.from(document.querySelectorAll<HTMLButtonElement>('#create-template-list button[data-template]'));
    const submitButton = byId<HTMLButtonElement>('create-submit');
    const resetButton = byId<HTMLButtonElement>('create-reset');

    requestTypeSelect.addEventListener('change', () => {
        renderCreateExtraFields(requestTypeSelect.value, getCreateExtraFieldValues());
    });

    templateButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const templateId = button.dataset.template as CreateTemplateId | undefined;
            if (!templateId || !createTemplates[templateId]) {
                return;
            }

            applyCreateTemplate(templateId);
            setCreateStatus('Modelo aplicado. Ajuste os dados e clique em Abrir Chamado.');
        });
    });

    resetButton.addEventListener('click', () => {
        form.reset();
        renderCreateExtraFields('');
        setCreateStatus('Selecione um modelo para preencher rapidamente.');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const title = formValue('create-title').trim();
        const requestType = requestTypeSelect.value;
        const areaIdValue = formValue('create-area');
        const description = formValue('create-description').trim();
        const priorityValue = formValue('create-priority');

        if (!title || !requestType || !areaIdValue || !description) {
            setCreateStatus('Preencha os campos obrigatorios para abrir o chamado.', true);
            return;
        }

        if (!isTicketPriority(priorityValue)) {
            setCreateStatus('Prioridade invalida.', true);
            return;
        }

        const originalLabel = submitButton.textContent ?? 'Abrir Chamado';
        submitButton.disabled = true;
        submitButton.textContent = 'Abrindo...';
        setCreateStatus('Enviando chamado em modo portfolio...');

        try {
            const response = await createTicket({
                title,
                request_type: requestType,
                area_id: Number(areaIdValue),
                priority: priorityValue,
                description,
                extra_fields: getCreateExtraFieldValues(true),
            });

            setCreateStatus(`Chamado ${response.ticket.code} aberto com sucesso.`, false, true);
            showFlash(response.message);
            form.reset();
            renderCreateExtraFields('');

            state.ticketsQuery.page = 1;
            await Promise.all([loadDashboard(), loadTickets(), loadKanban(), refreshNotificationCount()]);
            if (!byId<HTMLDivElement>('notifications-dropdown').classList.contains('hidden')) {
                await loadNotifications();
            }
        } catch (error) {
            const message = getErrorMessage(error, 'Nao foi possivel abrir o chamado.');
            setCreateStatus(message, true);
            showFlash(message, true);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalLabel;
        }
    });
}

function applyCreateTemplate(templateId: CreateTemplateId): void {
    const template = createTemplates[templateId];
    byId<HTMLInputElement>('create-title').value = template.title;
    byId<HTMLSelectElement>('create-request-type').value = template.request_type;
    byId<HTMLSelectElement>('create-area').value = String(template.area_id);
    byId<HTMLSelectElement>('create-priority').value = template.priority;
    byId<HTMLTextAreaElement>('create-description').value = template.description;
    renderCreateExtraFields(template.request_type, template.extra_fields);
}

function renderCreateExtraFields(requestType: string, presetValues: Record<string, string> = {}): void {
    const container = byId<HTMLDivElement>('create-extra-fields');
    container.innerHTML = '';

    const fields = createExtraFieldMap[requestType] ?? [];
    if (!fields.length) {
        return;
    }

    fields.forEach((field) => {
        const wrapper = document.createElement('div');
        const label = document.createElement('label');
        const input = document.createElement('input');

        wrapper.className = 'space-y-1';

        label.className = 'text-sm font-medium text-gray-700';
        label.textContent = field.label;
        label.htmlFor = `create-extra-${field.key}`;

        input.id = `create-extra-${field.key}`;
        input.type = 'text';
        input.placeholder = field.placeholder;
        input.className = 'form-input mt-1';
        input.dataset.extraKey = field.key;
        input.value = presetValues[field.key] ?? '';

        wrapper.appendChild(label);
        wrapper.appendChild(input);
        container.appendChild(wrapper);
    });
}

function getCreateExtraFieldValues(onlyFilled = false): Record<string, string> {
    const values: Record<string, string> = {};

    Array.from(document.querySelectorAll<HTMLInputElement>('#create-extra-fields input[data-extra-key]')).forEach((input) => {
        const key = input.dataset.extraKey ?? '';
        const value = input.value.trim();
        if (!key || (onlyFilled && !value)) {
            return;
        }
        values[key] = value;
    });

    return values;
}

function setCreateStatus(message: string, isError = false, isSuccess = false): void {
    const status = byId<HTMLParagraphElement>('create-ticket-status');
    status.textContent = message;
    status.classList.remove('text-gray-500', 'text-red-600', 'text-green-700');

    if (isError) {
        status.classList.add('text-red-600');
        return;
    }

    if (isSuccess) {
        status.classList.add('text-green-700');
        return;
    }

    status.classList.add('text-gray-500');
}

function isTicketPriority(value: string): value is TicketPriority {
    return value === 'low' || value === 'medium' || value === 'high' || value === 'critical';
}

function bindTickets(): void {
    const form = byId<HTMLFormElement>('tickets-filters');
    const body = byId<HTMLTableSectionElement>('tickets-table-body');

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

    body.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        const button = target.closest<HTMLElement>('[data-open-ticket-id]');
        if (!button) {
            return;
        }

        event.preventDefault();
        const ticketId = Number(button.dataset.openTicketId);
        if (!ticketId) {
            return;
        }

        void openTicketDetails(ticketId);
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
            const code = escapeHtml(ticket.code);
            const requestTypeLabel = escapeHtml(ticket.request_type_label);
            const title = escapeHtml(ticket.title);
            const areaName = escapeHtml(ticket.area.name);
            const statusLabel = escapeHtml(ticket.status_label);
            const priorityLabel = escapeHtml(ticket.priority_label);
            const slaLabel = escapeHtml(ticket.sla_label);
            const assigneeName = escapeHtml(ticket.assignee?.name ?? '-');
            const updatedHuman = escapeHtml(ticket.updated_human);

            return `
                <tr class="hover:bg-gray-50 align-top">
                    <td class="px-4 py-4 text-sm font-semibold">
                        <button type="button" data-open-ticket-id="${ticket.id}" class="text-primary-600 hover:underline text-left">
                            ${code}
                        </button>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${requestTypeClass}">${requestTypeLabel}</span>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-900 max-w-xs truncate">
                        <button type="button" data-open-ticket-id="${ticket.id}" class="hover:underline text-left truncate max-w-xs">
                            ${title}
                        </button>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${areaClass}">${areaName}</span>
                    </td>
                    <td class="px-4 py-4"><span class="badge ${ticket.status_badge_class}">${statusLabel}</span></td>
                    <td class="px-4 py-4"><span class="badge ${ticket.priority_badge_class}">${priorityLabel}</span></td>
                    <td class="px-4 py-4"><span class="badge ${slaClass}">${slaLabel}</span></td>
                    <td class="px-4 py-4 text-sm text-gray-600">${assigneeName}</td>
                    <td class="px-4 py-4 text-sm text-gray-500">${updatedHuman}</td>
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
    const board = byId<HTMLDivElement>('kanban-board');

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

    board.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        const button = target.closest<HTMLElement>('[data-open-ticket-id]');
        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const ticketId = Number(button.dataset.openTicketId);
        if (!ticketId) {
            return;
        }

        void openTicketDetails(ticketId);
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
                    ${columnTickets.map((ticket) => {
                        const code = escapeHtml(ticket.code);
                        const title = escapeHtml(ticket.title);
                        const priorityLabel = escapeHtml(ticket.priority_label);
                        const statusLabel = escapeHtml(ticket.status_label);
                        const requester = escapeHtml(ticket.requester);
                        const assignee = escapeHtml(ticket.assignee ?? 'Nao atribuido');
                        const updatedHuman = escapeHtml(ticket.updated_human);

                        return `
                            <article class="kanban-card ${ticket.card_class}" data-ticket-id="${ticket.id}">
                                <div class="p-4 space-y-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <button type="button" data-open-ticket-id="${ticket.id}" class="text-xs font-semibold text-primary-600 hover:underline">
                                            ${code}
                                        </button>
                                        <span class="badge ${ticket.priority_badge}">${priorityLabel}</span>
                                    </div>
                                    <h4 class="text-sm font-semibold text-gray-900 line-clamp-2">
                                        <button type="button" data-open-ticket-id="${ticket.id}" class="hover:underline text-left">
                                            ${title}
                                        </button>
                                    </h4>
                                    <div class="space-y-1 text-xs text-gray-500">
                                        <p><span class="text-gray-400">Status:</span> ${statusLabel}</p>
                                        <p><span class="text-gray-400">Solicitante:</span> ${requester}</p>
                                        <p><span class="text-gray-400">Responsavel:</span> ${assignee}</p>
                                        <p><span class="text-gray-400">Atualizado:</span> ${updatedHuman}</p>
                                    </div>
                                </div>
                            </article>
                        `;
                    }).join('')}
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
            filter: '[data-open-ticket-id]',
            preventOnFilter: false,
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
