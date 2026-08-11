<?php

namespace Services;

/**
 * Class CifraClubService
 * Serviço de integração com a Cifra Club API (code4music/cifraclub-api)
 * e fallback nativo em PHP para raspagem e extração de cifras.
 */
class CifraClubService {
    /** @var string URL base da Cifra Club API dockerizada. */
    private $apiUrl;

    /**
     * Construtor da classe CifraClubService.
     */
    public function __construct() {
        $this->apiUrl = getenv('CIFRACLUB_API_URL') ?: 'http://cifraclub-api:3000';
    }

    /**
     * Extrai o slug do artista e da música a partir de uma URL do Cifra Club.
     * Exemplo: https://www.cifraclub.com.br/coldplay/the-scientist/ -> ['artist' => 'coldplay', 'song' => 'the-scientist']
     *
     * @param string $url URL do Cifra Club.
     * @return array|false Retorna ['artist' => slug, 'song' => slug] ou false.
     */
    public function parseUrl($url) {
        $url = trim($url);
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) return false;

        $parts = array_values(array_filter(explode('/', $path)));
        if (count($parts) >= 2) {
            return [
                'artist' => strtolower($parts[0]),
                'song'   => strtolower($parts[1])
            ];
        }
        return false;
    }

    /**
     * Busca os dados e o texto da cifra do Cifra Club (via API REST ou scraping fallback).
     *
     * @param string $artist Slug ou nome do artista/banda.
     * @param string $song Slug ou nome da música.
     * @return array|false Dados da cifra ['artist', 'name', 'tom', 'cifra', 'url']
     */
    public function fetchCifra($artist, $song) {
        $artistSlug = $this->slugify($artist);
        $songSlug   = $this->slugify($song);

        if (empty($artistSlug) || empty($songSlug)) {
            return false;
        }

        // 1. Tenta consultar a API REST do code4music/cifraclub-api
        $cifraApiResult = $this->fetchFromApi($artistSlug, $songSlug);
        if ($cifraApiResult && !empty($cifraApiResult['cifra'])) {
            return $cifraApiResult;
        }

        // 2. Fallback resiliente: scraping direto da página do Cifra Club
        $url = "https://www.cifraclub.com.br/{$artistSlug}/{$songSlug}/";
        return $this->fetchFromWebScraper($url, $artistSlug, $songSlug);
    }

    /**
     * Busca a cifra a partir de uma URL completa do Cifra Club.
     *
     * @param string $url URL completa do Cifra Club.
     * @return array|false Dados da cifra.
     */
    public function fetchByUrl($url) {
        $url = trim($url);
        if (empty($url)) return false;

        $parsed = $this->parseUrl($url);
        $artistSlug = $parsed ? $parsed['artist'] : '';
        $songSlug   = $parsed ? $parsed['song'] : '';

        // Tenta API primeiro se tivermos os slugs
        if ($artistSlug && $songSlug) {
            $apiResult = $this->fetchFromApi($artistSlug, $songSlug);
            if ($apiResult && !empty($apiResult['cifra'])) {
                return $apiResult;
            }
        }

        // Caso contrário faz scraping direto da URL fornecida
        return $this->fetchFromWebScraper($url, $artistSlug, $songSlug);
    }

    /**
     * Requisita a cifra da API REST dockerizada testando endpoints candidatos (Docker / Localhost).
     *
     * @param string $artistSlug Slug do artista.
     * @param string $songSlug Slug da música.
     * @return array|false
     */
    private function fetchFromApi($artistSlug, $songSlug) {
        $candidateUrls = array_unique([
            rtrim($this->apiUrl, '/'),
            'http://cifraclub-api:3000',
            'http://127.0.0.1:3000',
            'http://localhost:3000'
        ]);

        foreach ($candidateUrls as $baseUrl) {
            $endpoint = "{$baseUrl}/artists/{$artistSlug}/songs/{$songSlug}";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 4,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    $cifraStr = is_array($data['cifra'] ?? null) 
                        ? implode("\n", $data['cifra']) 
                        : ($data['cifra'] ?? '');

                    if (!empty(trim($cifraStr))) {
                        return [
                            'artist' => $data['artist'] ?? ucfirst(str_replace('-', ' ', $artistSlug)),
                            'name'   => $data['name'] ?? ucfirst(str_replace('-', ' ', $songSlug)),
                            'tom'    => $data['tom'] ?? '',
                            'cifra'  => trim($cifraStr),
                            'url'    => $data['cifraclub_url'] ?? "https://www.cifraclub.com.br/{$artistSlug}/{$songSlug}/"
                        ];
                    }
                }
            }
        }

        return false;
    }

    /**
     * Scraping fallback nativo em PHP com desativação de validação SSL e múltiplos seletores HTML.
     *
     * @param string $url URL de destino.
     * @param string $artistSlug Slug do artista.
     * @param string $songSlug Slug da música.
     * @return array|false
     */
    private function fetchFromWebScraper($url, $artistSlug = '', $songSlug = '') {
        if (empty($url)) return false;

        // Garante protocolo https://
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_FOLLOWLOCATION => true
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($html)) {
            return false;
        }

        $cifraText = '';

        // Tenta capturar todas as ocorrências de <pre>
        if (preg_match_all('/<pre[^>]*>(.*?)<\/pre>/is', $html, $matches)) {
            $cifraText = implode("\n", $matches[1]);
        }
        
        // Fallbacks se <pre> não trouxer texto limpo
        if (empty(trim(strip_tags($cifraText)))) {
            if (preg_match('/id="cifra_cnt"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
                $cifraText = $matches[1];
            } elseif (preg_match('/<div[^>]*class="[^"]*cifra_cnt[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
                $cifraText = $matches[1];
            } elseif (preg_match('/<div[^>]*class="[^"]*cifra[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
                $cifraText = $matches[1];
            }
        }

        if (!empty(trim(strip_tags($cifraText)))) {
            // Limpa scripts e estilos inline
            $cifraText = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $cifraText);
            $cifraText = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $cifraText);
            $cifraText = strip_tags($cifraText);
            $cifraText = html_entity_decode($cifraText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Extrai Tom
            $tom = '';
            if (preg_match('/id="cifra_tom"[^>]*>(.*?)<\/a>/is', $html, $tomMatches)) {
                $tom = trim(strip_tags($tomMatches[1]));
            } elseif (preg_match('/class="[^"]*cifra-tom[^"]*"[^>]*>(.*?)<\/span>/is', $html, $tomMatches)) {
                $tom = trim(strip_tags($tomMatches[1]));
            }

            // Extrai Nomes
            $artistName = $artistSlug ? ucfirst(str_replace('-', ' ', $artistSlug)) : 'Artista';
            $songName   = $songSlug ? ucfirst(str_replace('-', ' ', $songSlug)) : 'Música';

            if (preg_match('/<h1[^>]*class="[^"]*t1[^"]*"[^>]*>(.*?)<\/h1>/is', $html, $titleMatches)) {
                $songName = trim(strip_tags($titleMatches[1]));
            } elseif (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $titleMatches)) {
                $songName = trim(strip_tags($titleMatches[1]));
            }

            if (preg_match('/<h2[^>]*class="[^"]*t3[^"]*"[^>]*>(.*?)<\/h2>/is', $html, $artistMatches)) {
                $artistName = trim(strip_tags($artistMatches[1]));
            }

            return [
                'artist' => $artistName,
                'name'   => $songName,
                'tom'    => $tom,
                'cifra'  => trim($cifraText),
                'url'    => $url
            ];
        }

        return false;
    }

    /**
     * Converte um texto ou nome em slug amigável para URL.
     *
     * @param string $text
     * @return string
     */
    private function slugify($text) {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return preg_replace('~-+~', '-', $text);
    }
}
