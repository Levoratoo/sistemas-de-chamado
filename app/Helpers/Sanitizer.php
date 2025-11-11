<?php

namespace App\Helpers;

class Sanitizer
{
    /**
     * Sanitiza string removendo tags HTML maliciosas e mantendo apenas formatação básica
     */
    public static function sanitize(string $html): string
    {
        // Remove todas as tags HTML, mantendo apenas o texto
        $text = strip_tags($html);
        
        // Decodifica entidades HTML
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        
        // Remove caracteres de controle e limpa espaços
        $text = trim($text);
        
        // Remove múltiplos espaços em branco
        $text = preg_replace('/\s+/', ' ', $text);
        
        return $text;
    }

    /**
     * Sanitiza permitindo formatação básica (p, br, strong, em)
     */
    public static function sanitizeWithFormatting(string $html): string
    {
        // Permite apenas tags seguras
        $allowed = '<p><br><strong><em><b><i><u>';
        
        $text = strip_tags($html, $allowed);
        
        // Remove atributos das tags permitidas
        $text = preg_replace('/<(p|br|strong|em|b|i|u)\s[^>]*>/', '<$1>', $text);
        
        return trim($text);
    }

    /**
     * Sanitiza para exibição segura (previne XSS)
     */
    public static function escape(string $html): string
    {
        return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
    }
}











