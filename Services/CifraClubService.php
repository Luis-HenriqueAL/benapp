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
        $this->apiUrl = getenv('CIFRACLUB_API_URL') ?: 'http://localhost:3000';
    }

    /**
     * Extrai o slug do artista e da música a partir de uma URL do Cifra Club.
     * Exemplo: https://www.cifraclub.com.br/coldplay/the-scientist/ -> ['coldplay', 'the-scientist']
     *
     * @param string $url URL do Cifra Club.
     * @return array|false Retorna ['artist' => slug, 'song' => slug] ou false.
     */
    public function parseUrl($url) {
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
        return $this->fetchFromWebScraper($artistSlug, $songSlug);
    }

    /**
     * Busca a cifra a partir de uma URL completa do Cifra Club.
     *
     * @param string $url URL completa do Cifra Club.
     * @return array|false Dados da cifra.
     */
    public function fetchByUrl($url) {
        $parsed = $this->parseUrl($url);
        if ($parsed) {
            return $this->fetchCifra($parsed['artist'], $parsed['song']);
        }
        return false;
    }

    /**
     * Requisita a cifra da API REST dockerizada.
     *
     * @param string $artistSlug Slug do artista.
     * @param string $songSlug Slug da música.
     * @return array|false
     */
    private function fetchFromApi($artistSlug, $songSlug) {
        $endpoint = rtrim($this->apiUrl, '/') . "/artists/{$artistSlug}/songs/{$songSlug}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
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

                if (!empty($cifraStr)) {
                    return [
                        'artist' => $data['artist'] ?? ucfirst(str_replace('-', ' ', $artistSlug)),
                        'name'   => $data['name'] ?? ucfirst(str_replace('-', ' ', $songSlug)),
                        'tom'    => $data['tom'] ?? '',
                        'cifra'  => $cifraStr,
                        'url'    => $data['cifraclub_url'] ?? "https://www.cifraclub.com.br/{$artistSlug}/{$songSlug}/"
                    ];
                }
            }
        }
        return false;
    }

    /**
     * Scraping fallback nativo em PHP caso a API esteja indisponível.
     *
     * @param string $artistSlug
     * @param string $songSlug
     * @return array|false
     */
    private function fetchFromWebScraper($artistSlug, $songSlug) {
        $url = "https://www.cifraclub.com.br/{$artistSlug}/{$songSlug}/";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_FOLLOWLOCATION => true
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($html)) {
            return false;
        }

        // Extrai o conteúdo do elemento <pre> (cifra)
        if (preg_match('/<pre[^>]*>(.*?)<\/pre>/is', $html, $matches)) {
            $rawCifra = $matches[1];
            // Limpa tags HTML internas mantendo texto monoespaçado
            $cifraText = strip_tags($rawCifra);
            $cifraText = html_entity_decode($cifraText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Extrai o Tom se disponível no HTML
            $tom = '';
            if (preg_match('/id="cifra_tom"[^>]*>(.*?)<\/a>/is', $html, $tomMatches)) {
                $tom = trim(strip_tags($tomMatches[1]));
            }

            // Extrai Título da Música e Artista se disponível no <h1 class="t1"> e <h2 class="t3">
            $artistName = ucfirst(str_replace('-', ' ', $artistSlug));
            $songName   = ucfirst(str_replace('-', ' ', $songSlug));

            if (preg_match('/<h1[^>]*class="[^"]*t1[^"]*"[^>]*>(.*?)<\/h1>/is', $html, $titleMatches)) {
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
