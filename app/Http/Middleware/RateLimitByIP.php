<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByIP
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): Response
    {
        $key = 'rate_limit_ip:' . $request->ip();
        
        // Configurações de rate limiting por endpoint
        $endpointLimits = [
            'tickets.store' => ['max' => 5, 'decay' => 15], // 5 tickets por 15 minutos
            'tickets.comment' => ['max' => 20, 'decay' => 5], // 20 comentários por 5 minutos
            'tickets.attach' => ['max' => 10, 'decay' => 10], // 10 anexos por 10 minutos
            'auth.login' => ['max' => 5, 'decay' => 15], // 5 tentativas de login por 15 minutos
            'default' => ['max' => (int)$maxAttempts, 'decay' => (int)$decayMinutes],
        ];

        // Determinar limite baseado na rota
        $routeName = $request->route()?->getName() ?? 'default';
        $limit = $endpointLimits[$routeName] ?? $endpointLimits['default'];
        
        $key = $key . ':' . $routeName;

        // Verificar se excedeu o limite
        if (RateLimiter::tooManyAttempts($key, $limit['max'])) {
            $retryAfter = RateLimiter::availableIn($key);
            
            // Log da tentativa de rate limiting
            \Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'route' => $routeName,
                'user_agent' => $request->userAgent(),
                'retry_after' => $retryAfter,
            ]);

            return response()->json([
                'error' => 'Muitas tentativas. Tente novamente em ' . $retryAfter . ' segundos.',
                'retry_after' => $retryAfter,
                'limit' => $limit['max'],
                'decay' => $limit['decay'],
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