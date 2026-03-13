export type TicketStatus = 'open' | 'in_progress' | 'waiting_user' | 'finalized';
export type TicketPriority = 'low' | 'medium' | 'high' | 'critical';
export type TicketColumn = 'queue' | 'in_progress' | 'waiting_user' | 'finalized';

export interface ChartDataset {
    label?: string;
    data: number[];
    borderColor?: string | string[];
    backgroundColor?: string | string[];
    borderWidth?: number;
    tension?: number;
}

export interface ChartResponse {
    labels: string[];
    datasets: ChartDataset[];
}

export interface SlaComplianceResponse {
    complianceRate: number;
    total: number;
    onTime: number;
    overdue: number;
    datasets: ChartDataset[];
}

export interface DemoNotification {
    id: string;
    type: string;
    read_at: string | null;
    created_at: string;
    data: {
        message: string;
        ticket_id?: number;
        ticket_type?: string;
        requester_name?: string;
        assignee_name?: string;
        priority?: TicketPriority;
        category?: string;
    };
}

export interface DemoUser {
    id: number;
    name: string;
    role: 'admin' | 'gestor' | 'atendente' | 'usuario';
}

export interface DemoArea {
    id: number;
    name: string;
}

export interface DemoTicket {
    id: number;
    code: string;
    title: string;
    description: string;
    status: TicketStatus;
    priority: TicketPriority;
    request_type: string;
    area: DemoArea;
    requester: DemoUser;
    assignee: DemoUser | null;
    assignee_id: number | null;
    resolution_by: number | null;
    resolution_summary: string | null;
    created_at: string;
    updated_at: string;
    resolved_at: string | null;
    due_at: string;
}

export interface TicketListQuery {
    search?: string;
    status?: TicketStatus | '';
    priority?: TicketPriority | '';
    request_type?: string;
    area_id?: number | '';
    assigned_to_me?: boolean;
    my_tickets?: boolean;
    sort_by?: 'code' | 'title' | 'created_at' | 'updated_at' | 'priority' | 'status';
    sort_dir?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface TicketListResponse {
    success: true;
    data: Array<
        DemoTicket & {
            status_label: string;
            status_badge_class: string;
            priority_label: string;
            priority_badge_class: string;
            request_type_label: string;
            sla_status: 'good' | 'warning' | 'overdue';
            sla_label: string;
            updated_human: string;
        }
    >;
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface CreateTicketPayload {
    title: string;
    description: string;
    request_type: string;
    area_id: number;
    priority: TicketPriority;
    extra_fields?: Record<string, string>;
}

export interface CreateTicketResponse {
    message: string;
    ticket: TicketListResponse['data'][number];
}

export interface DashboardSummary {
    totalTickets: number;
    ticketsThisMonth: number;
    slaOnTime: number;
    overdueTickets: number;
    nearDueTickets: number;
    avgResolutionTime: string;
    satisfactionRate: number;
    totalEvaluations: number;
    openTickets: number;
    inProgressTickets: number;
    waitingTickets: number;
    resolvedTickets: number;
    recentTickets: TicketListResponse['data'];
    topAttendants: Array<{ id: number; name: string; area: string; resolved: number }>;
}

export interface KanbanBoardResponse {
    columnsMeta: Record<TicketColumn, { label: string; description: string; bg: string; accent: string }>;
    tickets: KanbanTicket[];
}

export interface KanbanTicket {
    id: number;
    code: string;
    title: string;
    status: TicketStatus;
    status_label: string;
    priority: TicketPriority;
    priority_label: string;
    priority_badge: string;
    requester: string;
    assignee: string | null;
    assignee_id: number | null;
    area: string;
    created_at: string;
    updated_human: string;
    updated_ts: number;
    sla_status: 'good' | 'warning' | 'overdue';
    card_class: string;
    column: TicketColumn;
    description_html: string;
}

export interface KanbanUpdatePayload {
    column: TicketColumn;
    return_reason?: string;
    resolution_summary?: string;
}

export interface KanbanUpdateResponse {
    message: string;
    ticket: {
        id: number;
        status: TicketStatus;
        status_label: string;
        assignee: string | null;
        assignee_id: number | null;
        updated_human: string;
        updated_ts: number;
        sla_status: 'good' | 'warning' | 'overdue';
        card_class: string;
        column: TicketColumn;
    };
}

interface DemoError extends Error {
    status: number;
}

const NOW = new Date('2026-03-11T12:00:00.000Z');
const CURRENT_USER_ID = 2;

const areas: DemoArea[] = [
    { id: 1, name: 'Financeiro' },
    { id: 2, name: 'TI' },
    { id: 3, name: 'Compras' },
    { id: 4, name: 'Gente e Gestao' },
    { id: 5, name: 'Pre Impressao' },
    { id: 6, name: 'RR - Registro de Reclamacoes' },
];

const users: DemoUser[] = [
    { id: 1, name: 'Ana Martins', role: 'admin' },
    { id: 2, name: 'Pedro Levorato', role: 'atendente' },
    { id: 3, name: 'Carla Souza', role: 'gestor' },
    { id: 4, name: 'Bruno Lima', role: 'atendente' },
    { id: 5, name: 'Rafaela Costa', role: 'atendente' },
    { id: 6, name: 'Gabriel Dias', role: 'usuario' },
    { id: 7, name: 'Mariana Rocha', role: 'usuario' },
    { id: 8, name: 'Luciano Prado', role: 'usuario' },
];

const requestTypeLabels: Record<string, string> = {
    geral: 'Geral',
    reembolso: 'Solicitacao de Reembolso',
    adiantamento: 'Adiantamento a Fornecedores',
    pagamento_geral: 'Pagamento Geral',
    devolucao_clientes: 'Devolucao de Clientes',
    pagamento_importacoes: 'Pagamento de Importacoes',
    rh: 'RH',
    contabilidade: 'Contabilidade',
    suporte_ti: 'Suporte TI',
    compra: 'Pedido de Compra',
};

const requestTypeColors: Record<string, string> = {
    geral: 'bg-gray-100 text-gray-800',
    reembolso: 'bg-purple-100 text-purple-800',
    adiantamento: 'bg-orange-100 text-orange-800',
    pagamento_geral: 'bg-yellow-100 text-yellow-800',
    devolucao_clientes: 'bg-green-100 text-green-800',
    pagamento_importacoes: 'bg-blue-100 text-blue-800',
    rh: 'bg-indigo-100 text-indigo-800',
    contabilidade: 'bg-red-100 text-red-800',
    suporte_ti: 'bg-cyan-100 text-cyan-800',
    compra: 'bg-emerald-100 text-emerald-800',
};

const priorityOrder: Record<TicketPriority, number> = {
    low: 1,
    medium: 2,
    high: 3,
    critical: 4,
};

const statusOrder: Record<TicketStatus, number> = {
    open: 1,
    in_progress: 2,
    waiting_user: 3,
    finalized: 4,
};

const networkState = {
    failNextRequest: false,
};

const initialTickets = generateTickets();
const initialNotifications = generateNotifications();

let ticketsStore: DemoTicket[] = clone(initialTickets);
let notificationsStore: DemoNotification[] = clone(initialNotifications);

const kanbanColumns: KanbanBoardResponse['columnsMeta'] = {
    queue: {
        label: 'Fila',
        description: 'Chamados sem atribuicao.',
        bg: 'bg-indigo-50/70',
        accent: 'bg-gradient-to-r from-indigo-400/80 to-sky-400/80',
    },
    in_progress: {
        label: 'Em andamento',
        description: 'Chamados atribuidos e em trabalho.',
        bg: 'bg-amber-50/70',
        accent: 'bg-gradient-to-r from-amber-400/80 to-orange-400/80',
    },
    waiting_user: {
        label: 'Aguardando Solicitante',
        description: 'Chamados aguardando resposta do solicitante.',
        bg: 'bg-yellow-50/70',
        accent: 'bg-gradient-to-r from-yellow-400/80 to-orange-400/80',
    },
    finalized: {
        label: 'Finalizada',
        description: 'Chamados finalizados por voce.',
        bg: 'bg-emerald-50/70',
        accent: 'bg-gradient-to-r from-emerald-400/80 to-teal-400/80',
    },
};

export function resetPortfolioDemo(): void {
    ticketsStore = clone(initialTickets);
    notificationsStore = clone(initialNotifications);
    networkState.failNextRequest = false;
}

export function failNextDemoRequest(): void {
    networkState.failNextRequest = true;
}

export function getCurrentDemoUser(): DemoUser {
    return users.find((user) => user.id === CURRENT_USER_ID) ?? users[0];
}

export function getAreaColorClass(areaName: string): string {
    switch (areaName) {
        case 'Financeiro':
            return 'bg-green-100 text-green-800';
        case 'TI':
            return 'bg-blue-100 text-blue-800';
        case 'Compras':
            return 'bg-orange-100 text-orange-800';
        case 'Gente e Gestao':
            return 'bg-purple-100 text-purple-800';
        case 'Pre Impressao':
            return 'bg-red-100 text-red-800';
        case 'RR - Registro de Reclamacoes':
            return 'bg-orange-100 text-orange-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

export function getRequestTypeLabel(type: string): string {
    return requestTypeLabels[type] ?? 'Geral';
}

export function getRequestTypeClass(type: string): string {
    return requestTypeColors[type] ?? requestTypeColors.geral;
}

export async function getDashboardSummary(): Promise<DashboardSummary> {
    return simulateRequest(() => {
        const totalTickets = ticketsStore.length;
        const ticketsThisMonth = ticketsStore.filter((ticket) => {
            const created = new Date(ticket.created_at);
            return created.getUTCFullYear() === NOW.getUTCFullYear() && created.getUTCMonth() === NOW.getUTCMonth();
        }).length;

        const finalized = ticketsStore.filter((ticket) => ticket.status === 'finalized');
        const onTime = finalized.filter((ticket) => {
            if (!ticket.resolved_at) {
                return false;
            }
            return new Date(ticket.resolved_at).getTime() <= new Date(ticket.due_at).getTime();
        }).length;

        const overdueTickets = ticketsStore.filter((ticket) => ticket.status !== 'finalized' && getSlaStatus(ticket) === 'overdue').length;
        const nearDueTickets = ticketsStore.filter((ticket) => ticket.status !== 'finalized' && getSlaStatus(ticket) === 'warning').length;
        const slaOnTime = finalized.length ? Number(((onTime / finalized.length) * 100).toFixed(1)) : 0;

        const resolvedDurations = finalized
            .filter((ticket) => ticket.resolved_at)
            .map((ticket) => {
                const start = new Date(ticket.created_at).getTime();
                const end = new Date(ticket.resolved_at ?? ticket.updated_at).getTime();
                return Math.max(end - start, 0);
            });

        const avgResolutionMs = resolvedDurations.length
            ? resolvedDurations.reduce((sum, value) => sum + value, 0) / resolvedDurations.length
            : 0;

        const avgResolutionHours = Math.round(avgResolutionMs / (1000 * 60 * 60));

        const topAttendants = users
            .filter((user) => user.role === 'atendente' || user.role === 'gestor')
            .map((user) => {
                const resolved = finalized.filter((ticket) => ticket.assignee_id === user.id).length;
                const firstTicket = ticketsStore.find((ticket) => ticket.assignee_id === user.id);
                return {
                    id: user.id,
                    name: user.name,
                    area: firstTicket?.area.name ?? 'Geral',
                    resolved,
                };
            })
            .sort((a, b) => b.resolved - a.resolved)
            .slice(0, 5);

        const recentTickets = ticketsStore
            .slice()
            .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
            .slice(0, 8)
            .map(toTicketView);

        return {
            totalTickets,
            ticketsThisMonth,
            slaOnTime,
            overdueTickets,
            nearDueTickets,
            avgResolutionTime: `${Math.max(avgResolutionHours, 1)}h`,
            satisfactionRate: 94,
            totalEvaluations: finalized.length,
            openTickets: ticketsStore.filter((ticket) => ticket.status === 'open').length,
            inProgressTickets: ticketsStore.filter((ticket) => ticket.status === 'in_progress').length,
            waitingTickets: ticketsStore.filter((ticket) => ticket.status === 'waiting_user').length,
            resolvedTickets: finalized.length,
            recentTickets,
            topAttendants,
        };
    }, 'Nao foi possivel carregar os indicadores do dashboard.');
}

export async function getTrendChart(): Promise<ChartResponse> {
    return simulateRequest(() => {
        const labels: string[] = [];
        const createdData: number[] = [];
        const resolvedData: number[] = [];

        for (let offset = 29; offset >= 0; offset -= 1) {
            const date = addDays(startOfDay(NOW), -offset);
            const nextDate = addDays(date, 1);
            labels.push(formatDate(date, 'dd/MM'));

            createdData.push(
                ticketsStore.filter((ticket) => {
                    const created = new Date(ticket.created_at);
                    return created >= date && created < nextDate;
                }).length,
            );

            resolvedData.push(
                ticketsStore.filter((ticket) => {
                    if (!ticket.resolved_at) {
                        return false;
                    }
                    const resolved = new Date(ticket.resolved_at);
                    return resolved >= date && resolved < nextDate;
                }).length,
            );
        }

        return {
            labels,
            datasets: [
                {
                    label: 'Criados',
                    data: createdData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                },
                {
                    label: 'Resolvidos',
                    data: resolvedData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                },
            ],
        };
    }, 'Nao foi possivel carregar o grafico de tendencia.');
}

export async function getAreaDistributionChart(): Promise<ChartResponse> {
    return simulateRequest(() => {
        const grouped = areas.map((area) => ({
            label: area.name,
            total: ticketsStore.filter((ticket) => ticket.area.id === area.id).length,
        }));

        const colors = [
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(245, 158, 11)',
            'rgb(239, 68, 68)',
            'rgb(139, 92, 246)',
            'rgb(236, 72, 153)',
            'rgb(14, 165, 233)',
            'rgb(34, 197, 94)',
        ];

        return {
            labels: grouped.map((item) => item.label),
            datasets: [
                {
                    data: grouped.map((item) => item.total),
                    backgroundColor: colors.slice(0, grouped.length),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                },
            ],
        };
    }, 'Nao foi possivel carregar o grafico por area.');
}

export async function getSlaComplianceChart(): Promise<SlaComplianceResponse> {
    return simulateRequest(() => {
        const finalized = ticketsStore.filter((ticket) => ticket.status === 'finalized' && ticket.resolved_at);
        const onTime = finalized.filter((ticket) => new Date(ticket.resolved_at ?? ticket.updated_at).getTime() <= new Date(ticket.due_at).getTime()).length;
        const overdue = Math.max(finalized.length - onTime, 0);
        const complianceRate = finalized.length ? Number(((onTime / finalized.length) * 100).toFixed(1)) : 0;

        return {
            complianceRate,
            total: finalized.length,
            onTime,
            overdue,
            datasets: [
                {
                    data: [onTime, overdue],
                    backgroundColor: ['rgb(34, 197, 94)', 'rgb(239, 68, 68)'],
                    borderWidth: 0,
                },
            ],
        };
    }, 'Nao foi possivel carregar o grafico de SLA.');
}

export async function getAttendantPerformanceChart(): Promise<ChartResponse> {
    return simulateRequest(() => {
        const attendants = users
            .filter((user) => user.role === 'atendente' || user.role === 'gestor')
            .map((user) => ({
                name: user.name,
                resolved: ticketsStore.filter((ticket) => ticket.status === 'finalized' && ticket.assignee_id === user.id).length,
            }))
            .sort((a, b) => b.resolved - a.resolved)
            .slice(0, 10);

        return {
            labels: attendants.map((item) => item.name),
            datasets: [
                {
                    label: 'Chamados Resolvidos',
                    data: attendants.map((item) => item.resolved),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                },
            ],
        };
    }, 'Nao foi possivel carregar o ranking de atendentes.');
}

export async function getStatusDistributionChart(): Promise<ChartResponse> {
    return simulateRequest(() => {
        const statuses: Array<{ key: TicketStatus; label: string; color: string }> = [
            { key: 'open', label: 'Abertos', color: 'rgb(59, 130, 246)' },
            { key: 'in_progress', label: 'Em Progresso', color: 'rgb(245, 158, 11)' },
            { key: 'waiting_user', label: 'Aguardando', color: 'rgb(249, 115, 22)' },
            { key: 'finalized', label: 'Finalizados', color: 'rgb(34, 197, 94)' },
        ];

        const data = statuses
            .map((status) => ({ ...status, total: ticketsStore.filter((ticket) => ticket.status === status.key).length }))
            .filter((item) => item.total > 0);

        return {
            labels: data.map((item) => item.label),
            datasets: [
                {
                    data: data.map((item) => item.total),
                    backgroundColor: data.map((item) => item.color),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                },
            ],
        };
    }, 'Nao foi possivel carregar o grafico de status.');
}

export async function listTickets(query: TicketListQuery = {}): Promise<TicketListResponse> {
    return simulateRequest(() => {
        const perPage = clampNumber(query.per_page ?? 12, 5, 30);
        const page = Math.max(query.page ?? 1, 1);

        let items = ticketsStore.slice();

        if (query.search && query.search.trim().length > 0) {
            const search = query.search.trim().toLowerCase();
            items = items.filter((ticket) => {
                return ticket.code.toLowerCase().includes(search) || ticket.title.toLowerCase().includes(search);
            });
        }

        if (query.status) {
            items = items.filter((ticket) => ticket.status === query.status);
        }

        if (query.priority) {
            items = items.filter((ticket) => ticket.priority === query.priority);
        }

        if (query.request_type) {
            items = items.filter((ticket) => ticket.request_type === query.request_type);
        }

        if (query.area_id) {
            items = items.filter((ticket) => ticket.area.id === Number(query.area_id));
        }

        if (query.assigned_to_me) {
            items = items.filter((ticket) => ticket.assignee_id === CURRENT_USER_ID);
        }

        if (query.my_tickets) {
            items = items.filter((ticket) => ticket.requester.id === CURRENT_USER_ID);
        }

        const sortBy = query.sort_by ?? 'created_at';
        const sortDir = query.sort_dir ?? 'desc';

        items.sort((left, right) => {
            let result = 0;

            switch (sortBy) {
                case 'title':
                    result = left.title.localeCompare(right.title);
                    break;
                case 'code':
                    result = left.code.localeCompare(right.code);
                    break;
                case 'updated_at':
                    result = new Date(left.updated_at).getTime() - new Date(right.updated_at).getTime();
                    break;
                case 'priority':
                    result = priorityOrder[left.priority] - priorityOrder[right.priority];
                    break;
                case 'status':
                    result = statusOrder[left.status] - statusOrder[right.status];
                    break;
                case 'created_at':
                default:
                    result = new Date(left.created_at).getTime() - new Date(right.created_at).getTime();
                    break;
            }

            return sortDir === 'asc' ? result : result * -1;
        });

        const total = items.length;
        const lastPage = Math.max(Math.ceil(total / perPage), 1);
        const safePage = Math.min(page, lastPage);
        const startIndex = (safePage - 1) * perPage;
        const paginated = items.slice(startIndex, startIndex + perPage).map(toTicketView);

        return {
            success: true,
            data: paginated,
            pagination: {
                current_page: safePage,
                last_page: lastPage,
                per_page: perPage,
                total,
            },
        };
    }, 'Nao foi possivel carregar a listagem de chamados.');
}

export async function createTicket(payload: CreateTicketPayload): Promise<CreateTicketResponse> {
    return simulateRequest(() => {
        const title = payload.title?.trim() ?? '';
        const description = payload.description?.trim() ?? '';

        if (title.length < 5) {
            throw buildError('Informe um titulo com pelo menos 5 caracteres.', 422);
        }

        if (description.length < 10) {
            throw buildError('Informe uma descricao com pelo menos 10 caracteres.', 422);
        }

        if (!requestTypeLabels[payload.request_type]) {
            throw buildError('Tipo de solicitacao invalido.', 422);
        }

        const area = areas.find((item) => item.id === Number(payload.area_id));
        if (!area) {
            throw buildError('Area invalida para abertura do chamado.', 422);
        }

        if (!isTicketPriority(payload.priority)) {
            throw buildError('Prioridade invalida.', 422);
        }

        const nextId = ticketsStore.reduce((max, ticket) => Math.max(max, ticket.id), 0) + 1;
        const createdAt = NOW.toISOString();
        const dueAt = addDays(NOW, getDueDaysByPriority(payload.priority)).toISOString();

        const extraFields = Object.entries(payload.extra_fields ?? {})
            .map(([key, value]) => [key, String(value).trim()] as const)
            .filter(([, value]) => value.length > 0);

        const descriptionWithMetadata = [
            description,
            ...(extraFields.length
                ? ['', 'Campos adicionais:', ...extraFields.map(([key, value]) => `- ${formatExtraFieldLabel(key)}: ${value}`)]
                : []),
        ].join('\n');

        const ticket: DemoTicket = {
            id: nextId,
            code: buildTicketCode(nextId),
            title,
            description: descriptionWithMetadata,
            status: 'open',
            priority: payload.priority,
            request_type: payload.request_type,
            area,
            requester: getCurrentDemoUser(),
            assignee: null,
            assignee_id: null,
            resolution_by: null,
            resolution_summary: null,
            created_at: createdAt,
            updated_at: createdAt,
            resolved_at: null,
            due_at: dueAt,
        };

        ticketsStore = [ticket, ...ticketsStore];
        notificationsStore.unshift(createTicketCreatedNotification(ticket));

        return {
            message: `Chamado ${ticket.code} aberto com sucesso.`,
            ticket: toTicketView(ticket),
        };
    }, 'Nao foi possivel abrir o chamado.');
}

export async function getKanbanBoard(filters: Pick<TicketListQuery, 'search' | 'assigned_to_me' | 'my_tickets'> = {}): Promise<KanbanBoardResponse> {
    return simulateRequest(() => {
        let list = ticketsStore.slice();

        if (filters.search && filters.search.trim().length > 0) {
            const search = filters.search.trim().toLowerCase();
            list = list.filter((ticket) => ticket.code.toLowerCase().includes(search) || ticket.title.toLowerCase().includes(search));
        }

        if (filters.assigned_to_me) {
            list = list.filter((ticket) => ticket.assignee_id === CURRENT_USER_ID);
        }

        if (filters.my_tickets) {
            list = list.filter((ticket) => ticket.requester.id === CURRENT_USER_ID);
        }

        const tickets = list
            .map((ticket) => toKanbanTicket(ticket))
            .filter((ticket): ticket is KanbanTicket => ticket !== null)
            .sort((left, right) => right.updated_ts - left.updated_ts);

        return {
            columnsMeta: kanbanColumns,
            tickets,
        };
    }, 'Nao foi possivel carregar o Kanban.');
}

export async function updateKanbanStatus(ticketId: number, payload: KanbanUpdatePayload): Promise<KanbanUpdateResponse> {
    return simulateRequest(() => {
        const ticket = ticketsStore.find((item) => item.id === ticketId);

        if (!ticket) {
            throw buildError('Chamado nao encontrado.', 404);
        }

        const currentColumn = determineColumn(ticket);
        if (!currentColumn) {
            throw buildError('Chamado indisponivel para atualizacao neste quadro.', 422);
        }

        const targetColumn = payload.column;

        if (targetColumn === currentColumn) {
            return {
                message: 'O chamado ja esta na coluna selecionada.',
                ticket: toKanbanUpdateTicket(ticket),
            };
        }

        if (targetColumn === 'finalized') {
            const summary = payload.resolution_summary?.trim() ?? '';
            if (summary.length < 10) {
                throw buildError('Informe um resumo com pelo menos 10 caracteres.', 422);
            }

            if (!ticket.assignee_id) {
                ticket.assignee_id = CURRENT_USER_ID;
                ticket.assignee = getCurrentDemoUser();
            }

            ticket.status = 'finalized';
            ticket.resolution_by = CURRENT_USER_ID;
            ticket.resolution_summary = summary;
            ticket.resolved_at = NOW.toISOString();
            ticket.updated_at = NOW.toISOString();

            notificationsStore.unshift(createSystemNotification(ticket, 'ticket_finalized'));

            return {
                message: 'Chamado finalizado com sucesso.',
                ticket: toKanbanUpdateTicket(ticket),
            };
        }

        if (targetColumn === 'queue') {
            const reason = payload.return_reason?.trim() ?? '';
            if (reason.length < 5) {
                throw buildError('Informe o motivo com pelo menos 5 caracteres.', 422);
            }

            ticket.assignee_id = null;
            ticket.assignee = null;
            ticket.status = 'open';
            ticket.updated_at = NOW.toISOString();

            notificationsStore.unshift(createSystemNotification(ticket, 'ticket_returned'));

            return {
                message: 'Chamado devolvido para a fila.',
                ticket: toKanbanUpdateTicket(ticket),
            };
        }

        if (targetColumn === 'in_progress') {
            ticket.assignee_id = CURRENT_USER_ID;
            ticket.assignee = getCurrentDemoUser();
            ticket.status = 'in_progress';
            ticket.updated_at = NOW.toISOString();

            return {
                message: 'Chamado movido para Em andamento.',
                ticket: toKanbanUpdateTicket(ticket),
            };
        }

        ticket.assignee_id = CURRENT_USER_ID;
        ticket.assignee = getCurrentDemoUser();
        ticket.status = 'waiting_user';
        ticket.updated_at = NOW.toISOString();

        return {
            message: 'Chamado movido para Aguardando Solicitante.',
            ticket: toKanbanUpdateTicket(ticket),
        };
    }, 'Nao foi possivel atualizar o status no Kanban.');
}

export async function getUnreadCount(): Promise<{ count: number }> {
    return simulateRequest(() => {
        return {
            count: notificationsStore.filter((notification) => !notification.read_at).length,
        };
    }, 'Nao foi possivel atualizar o contador de notificacoes.');
}

export async function getRecentNotifications(): Promise<{ notifications: DemoNotification[] }> {
    return simulateRequest(() => {
        const notifications = notificationsStore
            .slice()
            .sort((left, right) => new Date(right.created_at).getTime() - new Date(left.created_at).getTime())
            .slice(0, 10);

        return { notifications };
    }, 'Nao foi possivel carregar as notificacoes recentes.');
}

export async function markNotificationAsRead(id: string): Promise<{ success: boolean }> {
    return simulateRequest(() => {
        const target = notificationsStore.find((notification) => notification.id === id);
        if (!target) {
            throw buildError('Notificacao nao encontrada.', 404);
        }

        target.read_at = NOW.toISOString();
        return { success: true };
    }, 'Nao foi possivel marcar a notificacao como lida.');
}

export async function markAllNotificationsAsRead(): Promise<{ success: boolean }> {
    return simulateRequest(() => {
        notificationsStore = notificationsStore.map((notification) => ({
            ...notification,
            read_at: notification.read_at ?? NOW.toISOString(),
        }));

        return { success: true };
    }, 'Nao foi possivel marcar todas as notificacoes como lidas.');
}

export function getDemoAreas(): DemoArea[] {
    return clone(areas);
}

export function getDemoRequestTypes(): Array<{ value: string; label: string }> {
    return Object.entries(requestTypeLabels).map(([value, label]) => ({ value, label }));
}

function simulateRequest<T>(resolver: () => T, defaultMessage: string): Promise<T> {
    const delay = Math.round(220 + Math.random() * 430);

    return new Promise((resolve, reject) => {
        setTimeout(() => {
            try {
                if (networkState.failNextRequest) {
                    networkState.failNextRequest = false;
                    throw buildError(defaultMessage, 500);
                }

                resolve(resolver());
            } catch (error) {
                if (isDemoError(error)) {
                    reject(error);
                    return;
                }

                reject(buildError(defaultMessage, 500));
            }
        }, delay);
    });
}

function toTicketView(ticket: DemoTicket): TicketListResponse['data'][number] {
    const slaStatus = getSlaStatus(ticket);

    return {
        ...clone(ticket),
        status_label: getStatusLabel(ticket.status),
        status_badge_class: getStatusBadgeClass(ticket.status),
        priority_label: getPriorityLabel(ticket.priority),
        priority_badge_class: getPriorityBadgeClass(ticket.priority),
        request_type_label: getRequestTypeLabel(ticket.request_type),
        sla_status: slaStatus,
        sla_label: getSlaLabel(ticket, slaStatus),
        updated_human: getRelativeTime(ticket.updated_at),
    };
}

function toKanbanTicket(ticket: DemoTicket): KanbanTicket | null {
    const column = determineColumn(ticket);
    if (!column) {
        return null;
    }

    return {
        id: ticket.id,
        code: ticket.code,
        title: ticket.title,
        status: ticket.status,
        status_label: getStatusLabel(ticket.status),
        priority: ticket.priority,
        priority_label: getPriorityLabel(ticket.priority),
        priority_badge: getPriorityBadgeClass(ticket.priority),
        requester: ticket.requester.name,
        assignee: ticket.assignee?.name ?? null,
        assignee_id: ticket.assignee_id,
        area: ticket.area.name,
        created_at: formatDate(new Date(ticket.created_at), 'dd/MM/yyyy HH:mm'),
        updated_human: getRelativeTime(ticket.updated_at),
        updated_ts: Math.floor(new Date(ticket.updated_at).getTime() / 1000),
        sla_status: getSlaStatus(ticket),
        card_class: ticket.status === 'finalized' ? 'card-green' : '',
        column,
        description_html: sanitizeDescription(ticket.description),
    };
}

function toKanbanUpdateTicket(ticket: DemoTicket): KanbanUpdateResponse['ticket'] {
    const column = determineColumn(ticket);

    if (!column) {
        throw buildError('Coluna invalida para este chamado.', 422);
    }

    return {
        id: ticket.id,
        status: ticket.status,
        status_label: getStatusLabel(ticket.status),
        assignee: ticket.assignee?.name ?? null,
        assignee_id: ticket.assignee_id,
        updated_human: getRelativeTime(ticket.updated_at),
        updated_ts: Math.floor(new Date(ticket.updated_at).getTime() / 1000),
        sla_status: getSlaStatus(ticket),
        card_class: ticket.status === 'finalized' ? 'card-green' : '',
        column,
    };
}

function determineColumn(ticket: DemoTicket): TicketColumn | null {
    if (ticket.status === 'finalized') {
        return ticket.resolution_by === CURRENT_USER_ID ? 'finalized' : null;
    }

    if (ticket.status === 'waiting_user' && ticket.assignee_id === CURRENT_USER_ID) {
        return 'waiting_user';
    }

    if (ticket.status === 'in_progress' && ticket.assignee_id === CURRENT_USER_ID) {
        return 'in_progress';
    }

    if (!ticket.assignee_id && ticket.status !== 'finalized') {
        return 'queue';
    }

    return null;
}

function getStatusLabel(status: TicketStatus): string {
    switch (status) {
        case 'open':
            return 'Aberto';
        case 'in_progress':
            return 'Em andamento';
        case 'waiting_user':
            return 'Aguardando usuario';
        case 'finalized':
            return 'Finalizado';
        default:
            return 'Desconhecido';
    }
}

function getPriorityLabel(priority: TicketPriority): string {
    switch (priority) {
        case 'low':
            return 'Baixa';
        case 'medium':
            return 'Media';
        case 'high':
            return 'Alta';
        case 'critical':
            return 'Critica';
        default:
            return 'Media';
    }
}

function getPriorityBadgeClass(priority: TicketPriority): string {
    switch (priority) {
        case 'low':
            return 'badge-info';
        case 'medium':
            return 'badge-warning';
        case 'high':
            return 'badge-danger';
        case 'critical':
            return 'badge-danger';
        default:
            return 'badge-info';
    }
}

function isTicketPriority(value: string): value is TicketPriority {
    return value === 'low' || value === 'medium' || value === 'high' || value === 'critical';
}

function getDueDaysByPriority(priority: TicketPriority): number {
    switch (priority) {
        case 'critical':
            return 2;
        case 'high':
            return 3;
        case 'medium':
            return 5;
        case 'low':
        default:
            return 7;
    }
}

function buildTicketCode(id: number): string {
    return `CH-${NOW.getUTCFullYear()}-${String(id).padStart(6, '0')}`;
}

function formatExtraFieldLabel(key: string): string {
    return key
        .replace(/_/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function getStatusBadgeClass(status: TicketStatus): string {
    switch (status) {
        case 'open':
            return 'badge-info';
        case 'in_progress':
            return 'badge-warning';
        case 'waiting_user':
            return 'badge-warning';
        case 'finalized':
            return 'badge-success';
        default:
            return 'badge-info';
    }
}

function getSlaStatus(ticket: DemoTicket): 'good' | 'warning' | 'overdue' {
    if (ticket.status === 'finalized') {
        return 'good';
    }

    const due = new Date(ticket.due_at).getTime();
    const now = NOW.getTime();

    if (now > due) {
        return 'overdue';
    }

    const hoursRemaining = (due - now) / (1000 * 60 * 60);
    if (hoursRemaining <= 24) {
        return 'warning';
    }

    return 'good';
}

function getSlaLabel(ticket: DemoTicket, slaStatus: 'good' | 'warning' | 'overdue'): string {
    if (slaStatus === 'overdue') {
        const overdueDays = Math.max(Math.floor((NOW.getTime() - new Date(ticket.due_at).getTime()) / (1000 * 60 * 60 * 24)), 1);
        return `Vencido ha ${overdueDays} ${overdueDays === 1 ? 'dia' : 'dias'}`;
    }

    if (slaStatus === 'warning') {
        return 'Proximo do vencimento';
    }

    const remainingDays = Math.max(Math.ceil((new Date(ticket.due_at).getTime() - NOW.getTime()) / (1000 * 60 * 60 * 24)), 1);
    return `${remainingDays} dias restantes`;
}

function sanitizeDescription(value: string): string {
    const escaped = value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');

    return escaped.replace(/\n/g, '<br>');
}

function generateTickets(): DemoTicket[] {
    const types = Object.keys(requestTypeLabels);
    const priorities: TicketPriority[] = ['low', 'medium', 'high', 'critical'];
    const list: DemoTicket[] = [];

    for (let index = 1; index <= 72; index += 1) {
        const area = areas[index % areas.length];
        const requester = users[5 + (index % 3)] ?? users[6];
        const assigneePool = [users[1], users[3], users[4]].filter(Boolean) as DemoUser[];
        const assigneeCandidate = assigneePool[index % assigneePool.length];

        const createdAt = addDays(NOW, -((index % 28) + 1));
        createdAt.setUTCHours((8 + (index % 10)) % 24, (index * 7) % 60, 0, 0);
        const dueAt = addDays(createdAt, 7);

        let status: TicketStatus;
        let assignee: DemoUser | null = null;
        let assigneeId: number | null = null;
        let resolutionBy: number | null = null;
        let resolvedAt: string | null = null;
        let resolutionSummary: string | null = null;

        if (index % 4 === 0) {
            status = 'in_progress';
            assignee = index % 2 === 0 ? users[1] : assigneeCandidate;
            assigneeId = assignee.id;
        } else if (index % 5 === 0) {
            status = 'waiting_user';
            assignee = users[1];
            assigneeId = assignee.id;
        } else if (index % 3 === 0) {
            status = 'finalized';
            assignee = assigneeCandidate;
            assigneeId = assignee.id;
            resolutionBy = assignee.id;
            const resolvedDate = addDays(createdAt, 2 + (index % 4));
            resolvedAt = resolvedDate.toISOString();
            resolutionSummary = 'Chamado finalizado com sucesso e validado com o solicitante.';
        } else {
            status = 'open';
        }

        const updatedAt = resolvedAt ? new Date(resolvedAt) : addDays(createdAt, 1 + (index % 3));

        const requestType = types[index % types.length];
        const priority = priorities[index % priorities.length];

        list.push({
            id: index,
            code: `CH-2026-${String(index).padStart(6, '0')}`,
            title: `${getRequestTypeLabel(requestType)} - ${area.name} - Solicitacao ${index}`,
            description: `Detalhamento da solicitacao ${index} para ${area.name}.\nEscopo validado com as areas envolvidas.`,
            status,
            priority,
            request_type: requestType,
            area,
            requester,
            assignee,
            assignee_id: assigneeId,
            resolution_by: resolutionBy,
            resolution_summary: resolutionSummary,
            created_at: createdAt.toISOString(),
            updated_at: updatedAt.toISOString(),
            resolved_at: resolvedAt,
            due_at: dueAt.toISOString(),
        });
    }

    return list;
}

function generateNotifications(): DemoNotification[] {
    const sourceTickets = initialTickets.slice(0, 20);

    return sourceTickets.map((ticket, index) => {
        const category = index % 3 === 0 ? 'ticket_finalized' : index % 3 === 1 ? 'ticket_assigned' : 'sla_warning';
        const type = category === 'ticket_finalized'
            ? 'App\\Notifications\\TicketFinalizedNotification'
            : category === 'ticket_assigned'
                ? 'App\\Notifications\\TicketAssignedNotification'
                : 'App\\Notifications\\SlaWarningNotification';

        const createdAt = addDays(NOW, -(index % 7));
        createdAt.setUTCHours(10 + (index % 8), (index * 11) % 60, 0, 0);

        return {
            id: `ntf-${index + 1}`,
            type,
            read_at: index % 4 === 0 ? null : addDays(createdAt, 1).toISOString(),
            created_at: createdAt.toISOString(),
            data: {
                message:
                    category === 'ticket_finalized'
                        ? `${ticket.code} foi finalizado com sucesso.`
                        : category === 'ticket_assigned'
                            ? `${ticket.code} foi atribuido para atendimento.`
                            : `${ticket.code} esta proximo do vencimento de SLA.`,
                ticket_id: ticket.id,
                ticket_type: getRequestTypeLabel(ticket.request_type),
                requester_name: ticket.requester.name,
                assignee_name: ticket.assignee?.name,
                priority: ticket.priority,
                category,
            },
        };
    });
}

function createSystemNotification(ticket: DemoTicket, category: 'ticket_finalized' | 'ticket_returned'): DemoNotification {
    return {
        id: `ntf-${Math.random().toString(16).slice(2, 10)}`,
        type: category === 'ticket_finalized'
            ? 'App\\Notifications\\TicketFinalizedNotification'
            : 'App\\Notifications\\TicketAssignedNotification',
        read_at: null,
        created_at: NOW.toISOString(),
        data: {
            message:
                category === 'ticket_finalized'
                    ? `${ticket.code} foi finalizado no Kanban.`
                    : `${ticket.code} foi devolvido para a fila.`,
            ticket_id: ticket.id,
            ticket_type: getRequestTypeLabel(ticket.request_type),
            requester_name: ticket.requester.name,
            assignee_name: ticket.assignee?.name,
            priority: ticket.priority,
            category,
        },
    };
}

function createTicketCreatedNotification(ticket: DemoTicket): DemoNotification {
    return {
        id: `ntf-${Math.random().toString(16).slice(2, 10)}`,
        type: 'App\\Notifications\\TicketCreatedNotification',
        read_at: null,
        created_at: NOW.toISOString(),
        data: {
            message: `${ticket.code} foi aberto em ${ticket.area.name}.`,
            ticket_id: ticket.id,
            ticket_type: getRequestTypeLabel(ticket.request_type),
            requester_name: ticket.requester.name,
            assignee_name: ticket.assignee?.name,
            priority: ticket.priority,
            category: 'ticket_created',
        },
    };
}

function addDays(date: Date, days: number): Date {
    const next = new Date(date.getTime());
    next.setUTCDate(next.getUTCDate() + days);
    return next;
}

function startOfDay(date: Date): Date {
    const value = new Date(date.getTime());
    value.setUTCHours(0, 0, 0, 0);
    return value;
}

function formatDate(value: Date, format: 'dd/MM' | 'dd/MM/yyyy HH:mm'): string {
    const day = String(value.getUTCDate()).padStart(2, '0');
    const month = String(value.getUTCMonth() + 1).padStart(2, '0');
    const year = String(value.getUTCFullYear());
    const hours = String(value.getUTCHours()).padStart(2, '0');
    const minutes = String(value.getUTCMinutes()).padStart(2, '0');

    if (format === 'dd/MM') {
        return `${day}/${month}`;
    }

    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

function getRelativeTime(timestamp: string): string {
    const value = new Date(timestamp).getTime();
    const diffMs = Math.max(NOW.getTime() - value, 0);
    const diffMinutes = Math.floor(diffMs / (1000 * 60));

    if (diffMinutes < 1) {
        return 'agora';
    }

    if (diffMinutes < 60) {
        return `ha ${diffMinutes} min`;
    }

    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) {
        return `ha ${diffHours} h`;
    }

    const diffDays = Math.floor(diffHours / 24);
    return `ha ${diffDays} dia${diffDays > 1 ? 's' : ''}`;
}

function clampNumber(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
}

function clone<T>(value: T): T {
    return JSON.parse(JSON.stringify(value)) as T;
}

function buildError(message: string, status: number): DemoError {
    const error = new Error(message) as DemoError;
    error.status = status;
    return error;
}

function isDemoError(value: unknown): value is DemoError {
    if (!value || typeof value !== 'object') {
        return false;
    }

    const maybeError = value as Partial<DemoError>;
    return typeof maybeError.message === 'string' && typeof maybeError.status === 'number';
}
