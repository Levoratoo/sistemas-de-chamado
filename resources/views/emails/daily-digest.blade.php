<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resumo Diário - Sistema de Chamados</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .category { margin-bottom: 30px; }
        .category-title { background: #e9ecef; padding: 10px; font-weight: bold; border-left: 4px solid #007bff; }
        .ticket { background: #fff; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 10px; border-radius: 4px; }
        .ticket-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .ticket-id { font-weight: bold; color: #007bff; }
        .ticket-priority { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .priority-critical { background: #dc3545; color: white; }
        .priority-high { background: #fd7e14; color: white; }
        .priority-medium { background: #ffc107; color: black; }
        .priority-low { background: #28a745; color: white; }
        .ticket-info { font-size: 14px; color: #6c757d; }
        .ticket-description { margin-top: 10px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; font-size: 12px; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Resumo Diário - Sistema de Chamados</h1>
            <p>Olá, <strong>{{ $user->name }}</strong>!</p>
            <p>Este é o resumo dos chamados para <strong>{{ $date }}</strong></p>
            <p><strong>Total de chamados relevantes:</strong> {{ $totalTickets }}</p>
            <p><strong>Período:</strong> Últimos 7 dias + próximos 7 dias</p>
            <p><strong>SLA Base:</strong> 7 dias úteis para todos os tipos</p>
        </div>

        @foreach($ticketsByCategory as $category)
            <div class="category">
                <div class="category-title">{{ $category['title'] }} ({{ count($category['tickets']) }})</div>
                
                @foreach($category['tickets'] as $ticket)
                    <div class="ticket">
                        <div class="ticket-header">
                            <span class="ticket-id">#{{ $ticket->id }} - {{ $ticket->title }}</span>
                            <span class="ticket-priority priority-{{ $ticket->priority }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                        
                        <div class="ticket-info">
                            <strong>Tipo:</strong> {{ ucfirst($ticket->request_type ?? 'Geral') }} |
                            <strong>Área:</strong> {{ $ticket->area->name ?? 'N/A' }} |
                            <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </div>
                        
                        @if($ticket->assignee)
                            <div class="ticket-info">
                                <strong>Responsável:</strong> {{ $ticket->assignee->name }}
                            </div>
                        @endif
                        
                        @if($ticket->due_at)
                            <div class="ticket-info">
                                <strong>Vence em:</strong> {{ $ticket->due_at->format('d/m/Y H:i') }}
                                @if($ticket->due_at->isPast())
                                    <span style="color: #dc3545; font-weight: bold;">(VENCIDO)</span>
                                @elseif($ticket->due_at->isBefore(now()->addHours(2)))
                                    <span style="color: #fd7e14; font-weight: bold;">(PRÓXIMO DO VENCIMENTO)</span>
                                @endif
                            </div>
                        @endif
                        
                        <div class="ticket-description">
                            <strong>Descrição:</strong> {{ Str::limit($ticket->description, 200) }}
                        </div>
                        
                        <div style="margin-top: 10px;">
                            <a href="{{ route('tickets.show', $ticket) }}" class="btn">Ver Chamado</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="footer">
            <p>Este é um resumo automático do Sistema de Chamados.</p>
            <p>Para parar de receber estes emails, entre em contato com o administrador.</p>
            <p>Sistema desenvolvido por Pedro Levorato</p>
        </div>
    </div>
</body>
</html>
