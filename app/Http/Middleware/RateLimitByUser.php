<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '30', string $decayMinutes = '1'): Response
    {
        $user = $request->user();
        
        // Se não estiver autenticado, usar rate limiting por IP
        if (!$user) {
            return $next($request);
        }

        $key = 'rate_limit_user:' . $user->id;
        
        // Configurações de rate limiting por usuário e endpoint
        $endpointLimits = [
            'tickets.store' => ['max' => 10, 'decay' => 60], // 10 tickets por hora
            'tickets.comment' => ['max' => 50, 'decay' => 15], // 50 comentários por 15 minutos
            'tickets.attach' => ['max' => 20, 'decay' => 30], // 20 anexos por 30 minutos
            'tickets.assign' => ['max' => 30, 'decay' => 10], // 30 atribuições por 10 minutos
            'tickets.delegate' => ['max' => 20, 'decay' => 15], // 20 delegações por 15 minutos
            'default' => ['max' => (int)$maxAttempts, 'decay' => (int)$decayMinutes],
        ];

        // Determinar limite baseado na rota
        $routeName = $request->route()?->getName() ?? 'default';
        $limit = $endpointLimits[$routeName] ?? $endpointLimits['default'];
        
        $key = $key . ':' . $routeName;

        // Verificar se excedeu o limite
        if (RateLimiter::tooManyAttempts($key, $limit['max'])) {
            $retryAfter = RateLimiter::availableIn($key);
            
            // Log da tentativa de rate limiting por usuário
            \Log::warning('User rate limit exceeded', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'route' => $routeName,
                'retry_after' => $retryAfter,
            ]);

            return response()->json([
                'error' => 'Você fez muitas tentativas. Tente novamente em ' . $retryAfter . ' segundos.',
                'retry_after' => $retryAfter,
                'limit' => $limit['max'],
                'decay' => $limit['decay'],
                'user_id' => $user->id,
            ], 429)->header('Retry-After', $retryAfter);
        }

        // Incrementar contador
        RateLimiter::hit($key, $limit['decay'] * 60);

        // Adicionar headers informativos
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $limit['max']);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $limit['max']));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($limit['decay'])->timestamp);

        return $response;
    }
}