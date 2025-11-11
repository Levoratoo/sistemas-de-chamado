<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SpamProtection
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar apenas em requests POST/PUT/PATCH
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        // Verificar padrões de spam em campos de texto
        $spamPatterns = [
            '/\b(viagra|cialis|casino|poker|loan|credit|debt|free money|click here)\b/i',
            '/\b(bitcoin|cryptocurrency|investment|profit|earn money)\b/i',
            '/\b(seo|marketing|advertising|promotion|discount)\b/i',
            '/\b(winner|congratulations|prize|lucky|selected)\b/i',
            '/\b(urgent|immediate|act now|limited time|expires)\b/i',
        ];

        $textFields = [
            'title',
            'description',
            'comment',
            'message',
            'content',
        ];

        $spamScore = 0;
        $detectedPatterns = [];

        foreach ($textFields as $field) {
            $value = $request->input($field, '');
            if (empty($value)) continue;

            foreach ($spamPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $spamScore++;
                    $detectedPatterns[] = $pattern;
                }
            }
        }

        // Verificar repetição excessiva de caracteres
        foreach ($textFields as $field) {
            $value = $request->input($field, '');
            if (empty($value)) continue;

            // Verificar repetição de caracteres (ex: "aaaaaaaa")
            if (preg_match('/(.)\1{4,}/', $value)) {
                $spamScore += 2;
            }

            // Verificar repetição de palavras (ex: "test test test test")
            $words = explode(' ', $value);
            $wordCounts = array_count_values($words);
            foreach ($wordCounts as $word => $count) {
                if ($count > 3 && strlen($word) > 3) {
                    $spamScore += 1;
                }
            }
        }

        // Verificar URLs suspeitas
        $urlPattern = '/https?:\/\/[^\s]+/i';
        foreach ($textFields as $field) {
            $value = $request->input($field, '');
            if (preg_match_all($urlPattern, $value, $matches)) {
                foreach ($matches[0] as $url) {
                    $suspiciousDomains = [
                        'bit.ly', 'tinyurl.com', 'goo.gl', 't.co',
                        'short.link', 'cutt.ly', 'is.gd', 'v.gd'
                    ];
                    
                    foreach ($suspiciousDomains as $domain) {
                        if (strpos($url, $domain) !== false) {
                            $spamScore += 2;
                        }
                    }
                }
            }
        }

        // Se score de spam for alto, bloquear
        if ($spamScore >= 3) {
            \Log::warning('Spam detected and blocked', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'spam_score' => $spamScore,
                'detected_patterns' => $detectedPatterns,
                'request_data' => $request->except(['password', 'password_confirmation']),
            ]);

            return response()->json([
                'error' => 'Conteúdo suspeito detectado. Sua solicitação foi bloqueada.',
                'spam_score' => $spamScore,
                'message' => 'Se você acredita que isso é um erro, entre em contato com o administrador.',
            ], 422);
        }

        // Se score for moderado, adicionar delay
        if ($spamScore >= 1) {
            $delayKey = 'spam_delay:' . $request->ip();
            if (Cache::has($delayKey)) {
                return response()->json([
                    'error' => 'Aguarde alguns segundos antes de fazer outra solicitação.',
                    'retry_after' => Cache::get($delayKey),
                ], 429);
            }
            
            Cache::put($delayKey, 5, 60); // 5 segundos de delay
        }

        return $next($request);
    }
}