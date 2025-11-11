<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alerta Urgente - Sistema de Chamados</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .urgent-ticket { background: #fff; border: 2px solid #dc3545; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .ticket-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .ticket-id { font-weight: bold; color: #dc3545; font-size: 18px; }
        .urgent-badge { background: #dc3545; color: white; padding: 8px 12px; border-radius: 4px; font-weight: bold; }
        .ticket-info { font-size: 14px; color: #6c757d; margin-bottom: 10px; }
        .ticket-description { margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        .btn { display: inline-block; padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; font-weight: bold; }
        .btn:hover { background: #c82333; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; font-size: 12px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 ALERTA URGENTE</h1>
            <p>Sistema de Chamados - {{ $date }}</p>
        </div>

        <div class="warning">
            <strong>⚠️ ATENÇÃO:</strong> Você tem <strong>{{ $urgentTickets->count() }}</strong> chamado(s) que requerem ação imediata!
        </div>

        @foreach($urgentTickets as $ticket)
            <div class="urgent-ticket">
                <div class="ticket-header">
                    <span class="ticket-id">#{{ $ticket->id }} - {{ $ticket->title }}</span>
                    <span class="urgent-badge">URGENTE</span>
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
                            <span style="color: #dc3545; font-weight: bold; font-size: 16px;">(VENCIDO HÁ {{ $ticket->due_at->diffInHours(now()) }} HORAS)</span>
                        @else
                            <span style="color: #fd7e14; font-weight: bold;">(VENCE EM {{ $ticket->due_at->diffInHours(now()) }} HORAS)</span>
                        @endif
                    </div>
                @endif

                @if($ticket->requester)
                    <div class="ticket-info">
                        <strong>Solicitante:</strong> {{ $ticket->requester->name }}
                    </div>
                @endif
                
                <div class="ticket-description">
                    <strong>Descrição:</strong><br>
                    {{ $ticket->description }}
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="{{ route('tickets.show', $ticket) }}" class="btn">🚨 ATENDER AGORA</a>
                </div>
            </div>
        @endforeach

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ route('tickets.index') }}" class="btn" style="background: #007bff;">📋 Ver Fila de Chamados</a>
        </div>

        <div class="footer">
            <p><strong>Este é um alerta automático do Sistema de Chamados.</strong></p>
            <p>Para parar de receber estes alertas, entre em contato com o administrador.</p>
            <p>Sistema desenvolvido por Pedro Levorato</p>
        </div>
    </div>
</body>
</html>
