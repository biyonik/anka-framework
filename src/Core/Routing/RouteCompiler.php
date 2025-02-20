<?php

declare(strict_types=1);

namespace Framework\Core\Routing;

use Framework\Core\Routing\Interfaces\RouteInterface;

/**
 * Route pattern'lerini derlemeye yarayan yardımcı sınıf.
 *
 * Bu sınıf, route pattern'lerini regex formatına dönüştürür ve
 * parametre isimlerini yakalar. Ayrıca URL oluşturma ve
 * parametre eşleştirme işlemleri için yardımcı metodlar sunar.
 *
 * @package Framework\Core\Routing
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RouteCompiler
{
    /**
     * Default parametre pattern'i.
     */
    protected const DEFAULT_PATTERN = '[^/]+';

    /**
     * Route pattern'ini derler.
     *
     * @param string $pattern Derlenecek pattern
     * @param array<string,string> $wheres Parametre pattern'leri
     * @return array{0: string, 1: array<string>} Derlenmiş pattern ve parametre isimleri
     */
    public static function compile(string $pattern, array $wheres = []): array
    {
        // Parametre isimlerini topla
        preg_match_all('/{([^}]+)}/', $pattern, $matches);
        $paramNames = $matches[1];

        // Her bir parametre için pattern uygula
        $compiledPattern = preg_replace_callback(
            '/{([^}]+)}/',
            function ($match) use ($wheres) {
                $name = $match[1];
                // Özel pattern var mı kontrol et
                $pattern = $wheres[$name] ?? self::DEFAULT_PATTERN;
                return '(?P<' . $name . '>' . $pattern . ')';
            },
            $pattern
        );

        // Pattern'i regex'e çevir
        return ['#^' . $compiledPattern . '$#', $paramNames];
    }

    /**
     * Derlenmiş pattern ile verilen path'i eşleştirir.
     *
     * @param string $compiledPattern Derlenmiş pattern
     * @param string $path Eşleştirilecek path
     * @return array<string,string>|false Eşleşen parametreler veya false
     */
    public static function match(string $compiledPattern, string $path): array|false
    {
        if (preg_match($compiledPattern, $path, $matches) !== 1) {
            return false;
        }

        // Numerik indeksleri temizle
        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                unset($matches[$key]);
            }
        }

        return $matches;
    }

    /**
     * Pattern'deki parametreleri verilen değerlerle değiştirerek URL oluşturur.
     *
     * @param string $pattern Route pattern'i
     * @param array<string,mixed> $parameters Parametre değerleri
     * @return string Oluşturulan URL
     */
    public static function generateUrl(string $pattern, array $parameters): string
    {
        // Parametre isimlerini regex ile bul
        preg_match_all('/{([^}]+)}/', $pattern, $matches);

        // Her bir parametreyi değiştir
        foreach ($matches[0] as $index => $match) {
            $name = $matches[1][$index];

            if (!isset($parameters[$name])) {
                throw new \InvalidArgumentException("Missing parameter '$name'");
            }

            $pattern = str_replace($match, (string) $parameters[$name], $pattern);
            unset($parameters[$name]);
        }

        // Kalan parametreleri query string olarak ekle
        if (!empty($parameters)) {
            $pattern .= '?' . http_build_query($parameters);
        }

        return $pattern;
    }

    /**
     * URL pattern'indeki parametreleri çıkarır.
     *
     * @param string $pattern URL pattern'i
     * @return array<string> Parametre isimleri
     */
    public static function extractParameters(string $pattern): array
    {
        preg_match_all('/{([^}]+)}/', $pattern, $matches);
        return $matches[1];
    }

    /**
     * URL'i normalize eder.
     *
     * @param string $url Normalize edilecek URL
     * @return string Normalize edilmiş URL
     */
    public static function normalizeUrl(string $url): string
    {
        // Başta ve sonda slashları temizle
        $url = trim($url, '/');

        // Çift slashları tekli hale getir
        $url = preg_replace('#//+#', '/', $url);

        // Başa slash ekle
        return '/' . $url;
    }
}