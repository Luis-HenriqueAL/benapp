<?php
/**
 * View: Visualizador de Cifras e Letras com Autorolagem, Modo Letra e Transposição
 *
 * @var array $musicas Lista de músicas com cifras vinculadas ao evento.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

ob_start();

// Prepara IDs das músicas para JS
$musicaIds = array_map(fn($m) => (int) $m['id'], $musicas ?? []);
$musicaIdsJson = json_encode($musicaIds);
$musicaInicialJson = (int) ($musicaInicial ?? 0);
?>
<div class="max-w-2xl mx-auto pb-32 space-y-5">

    <!-- Header Fixo -->
    <div
        class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm rounded-b-3xl border-b border-slate-100 shadow-lg shadow-slate-200/30 px-5 py-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <!-- Voltar + Título -->
            <div class="flex items-center gap-3">
                <a href="javascript:history.back()"
                    class="p-2 rounded-2xl bg-slate-50 hover:bg-purple-50 text-slate-600 hover:text-purple-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">🎵 Cifras & Letras</h2>
                    <p id="subtitle-musica" class="text-[10px] text-slate-400 font-medium">Louvor do Encontro</p>
                </div>
            </div>

            <!-- Controles -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Modo Exibição -->
                <div class="flex items-center gap-1 bg-purple-50 p-1 rounded-2xl border border-purple-100">
                    <button id="btn-modo-cifra" type="button" onclick="setModoExibicao('cifra')"
                        class="px-3 py-1.5 rounded-xl bg-purple-600 text-white font-extrabold text-xs shadow-xs transition active:scale-95">
                        🎼 Cifra
                    </button>
                    <button id="btn-modo-letra" type="button" onclick="setModoExibicao('letra')"
                        class="px-3 py-1.5 rounded-xl bg-transparent text-purple-700 hover:bg-purple-100 font-bold text-xs transition active:scale-95">
                        📝 Letra
                    </button>
                </div>

                <!-- Tamanho de Fonte -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl border border-slate-200">
                    <button type="button" onclick="alterarFonte(-1)"
                        class="w-7 h-7 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-black text-xs shadow-xs transition active:scale-90 flex items-center justify-center"
                        title="Diminuir fonte">A-</button>
                    <button type="button" onclick="alterarFonte(1)"
                        class="w-7 h-7 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-black text-xs shadow-xs transition active:scale-90 flex items-center justify-center"
                        title="Aumentar fonte">A+</button>
                </div>
            </div>
        </div>

        <!-- Barra de Autorolagem + Próxima Música -->
        <div class="space-y-2.5 mt-3 pt-3 border-t border-slate-100">
            <!-- Botões de Ação -->
            <div class="flex items-center justify-between gap-2">
                <button id="btn-autoroll" type="button" onclick="toggleAutoScroll()"
                    class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 border border-slate-200 font-bold text-xs transition active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                    <span id="btn-autoroll-label">Iniciar Autorolagem</span>
                </button>

                <?php if (count($musicas ?? []) > 1): ?>
                    <button type="button" onclick="irParaProximaMusica()" id="btn-proxima"
                        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-extrabold text-xs transition active:scale-95">
                        Próxima
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Seletor de Velocidade (Abaixo dos botões) -->
            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 w-full">
                <span class="text-[11px] font-extrabold text-slate-500 whitespace-nowrap shrink-0">Velocidade:</span>
                <input id="scroll-speed" type="range" min="1" max="10" value="4" oninput="updateScrollSpeed(this.value)"
                    class="flex-1 min-w-0 w-full accent-purple-600 h-1.5 cursor-pointer rounded-full" title="Velocidade de rolagem">
                <span id="scroll-speed-label"
                    class="text-xs font-black text-purple-700 bg-purple-100 px-2 py-0.5 rounded-md shrink-0 text-center min-w-[32px]">4x</span>
            </div>
        </div>
    </div>

    <?php if (empty($musicas)): ?>
        <div class="bg-white rounded-3xl p-10 text-center space-y-3 border border-slate-100 shadow-sm">
            <div
                class="w-14 h-14 bg-purple-50 text-purple-600 rounded-3xl flex items-center justify-center mx-auto text-2xl">
                🎵</div>
            <h3 class="text-base font-extrabold text-slate-800">Nenhuma cifra cadastrada</h3>
            <p class="text-xs text-slate-400 max-w-xs mx-auto">Nenhuma música com cifra foi selecionada para este encontro
                ainda.</p>
            <a href="javascript:history.back()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-purple-500/20 transition active:scale-95 mt-2">
                Voltar para a Escala
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6" id="lista-musicas">
            <?php foreach ($musicas as $index => $m): ?>
                <?php
                $musicaId = (int) $m['id'];
                $tomOriginal = !empty($m['tom']) ? SecurityHelper::e($m['tom']) : 'C';
                ?>
                <div id="musica-card-<?= $musicaId ?>"
                    class="musica-card bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden space-y-4 p-5">

                    <!-- Cabeçalho da Música -->
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="space-y-1 min-w-0">
                            <span
                                class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 bg-purple-50 border border-purple-100 px-2 py-0.5 rounded-lg">
                                Música <?= $index + 1 ?>/<?= count($musicas) ?>
                            </span>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight leading-snug">
                                <?= SecurityHelper::e($m['titulo']) ?>
                            </h3>
                            <?php if (!empty($m['artista'])): ?>
                                <p class="text-xs font-bold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <?= SecurityHelper::e($m['artista']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($m['cifraclub_url'])): ?>
                            <a href="<?= SecurityHelper::e($m['cifraclub_url']) ?>" target="_blank" rel="noopener noreferrer"
                                class="text-[10px] font-bold text-purple-600 hover:text-purple-800 flex items-center gap-1 hover:underline shrink-0 mt-1">
                                Cifra Club
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($m['cifra_texto'])): ?>
                        <!-- Painel de Transposição -->
                        <div id="painel-tom-<?= $musicaId ?>"
                            class="bg-amber-50 border border-amber-200/80 p-3 rounded-2xl flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-extrabold text-amber-900">Tom:</span>
                                <span id="tom-display-<?= $musicaId ?>"
                                    class="px-2.5 py-1 rounded-xl bg-amber-600 text-white font-black text-xs tracking-wide"
                                    data-original-tom="<?= $tomOriginal ?>">
                                    <?= $tomOriginal ?>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="transporMusica(<?= $musicaId ?>, -1)"
                                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-amber-50 border border-amber-300 text-amber-900 font-extrabold text-xs active:scale-95 transition">-1
                                    Semi</button>
                                <button type="button" onclick="transporMusica(<?= $musicaId ?>, 1)"
                                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-amber-50 border border-amber-300 text-amber-900 font-extrabold text-xs active:scale-95 transition">+1
                                    Semi</button>
                                <button type="button" onclick="resetarTomMusica(<?= $musicaId ?>)"
                                    class="px-2.5 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold text-[10px] active:scale-95 transition">Resetar</button>
                            </div>
                        </div>

                        <!-- Bloco da Cifra -->
                        <div class="cifra-wrapper rounded-2xl border border-slate-200 overflow-hidden">
                            <pre id="cifra-pre-<?= $musicaId ?>" data-original-text="<?= SecurityHelper::e($m['cifra_texto']) ?>"
                                class="cifra-box font-mono text-xs md:text-sm leading-loose overflow-x-auto p-5 bg-slate-50 text-slate-800 whitespace-pre-wrap font-medium"><?= SecurityHelper::e($m['cifra_texto']) ?></pre>
                        </div>

                    <?php else: ?>
                        <div class="bg-slate-50 rounded-2xl p-6 text-center space-y-3 border border-slate-100">
                            <p class="text-xs text-slate-500 font-medium">A cifra ainda não foi carregada para esta música.</p>
                            <?php if (!empty($m['cifraclub_url'])): ?>
                                <a href="?id=<?= $musicaId ?>&force_refresh=1"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-xs transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                    Buscar Cifra no Cifra Club
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // ──────────────────────────────────────────────────────
    // Estado Global
    // ──────────────────────────────────────────────────────
    const MUSICA_IDS = <?= $musicaIdsJson ?>;
    let musicaAtualIndex = <?= $musicaInicialJson ?>;
    let modoExibicaoAtual = 'cifra';
    let currentFontSize = 14;
    let autoScrollTimer = null;
    let scrollSpeedLevel = 4;
    const musicSemitoneOffsets = {};

    // ──────────────────────────────────────────────────────
    // Ao carregar, rola até a música inicial (quando vier de ?id=X)
    document.addEventListener('DOMContentLoaded', () => {
        if (musicaAtualIndex > 0 && MUSICA_IDS.length > 0) {
            const id = MUSICA_IDS[musicaAtualIndex];
            const card = document.getElementById('musica-card-' + id);
            if (card) setTimeout(() => card.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
        }
        atualizarSubtitle();
    });

    function atualizarSubtitle() {
        const subtitle = document.getElementById('subtitle-musica');
        if (subtitle && MUSICA_IDS.length > 1) {
            subtitle.textContent = `Música ${musicaAtualIndex + 1} de ${MUSICA_IDS.length}`;
        }
    }

    // Navegação entre Músicas
    // ──────────────────────────────────────────────────────
    function irParaProximaMusica() {
        const total = MUSICA_IDS.length;
        if (total <= 1) return;

        musicaAtualIndex = (musicaAtualIndex + 1) % total;
        const nextId = MUSICA_IDS[musicaAtualIndex];
        const nextCard = document.getElementById('musica-card-' + nextId);
        if (nextCard) nextCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        atualizarSubtitle();
    }

    // ──────────────────────────────────────────────────────
    // Autorolagem
    // ──────────────────────────────────────────────────────
    function toggleAutoScroll() {
        if (autoScrollTimer) {
            stopAutoScroll();
        } else {
            startAutoScroll();
        }
    }

    function startAutoScroll() {
        const btn = document.getElementById('btn-autoroll');
        const label = document.getElementById('btn-autoroll-label');

        if (btn) {
            btn.classList.remove('bg-slate-50', 'hover:bg-emerald-50', 'text-slate-600', 'hover:text-emerald-700', 'border-slate-200');
            btn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
        }
        if (label) label.textContent = 'Parar Autorolagem';

        // scrollSpeedLevel 1-10: autorolagem mais rápida em todos os níveis (ex: nível 1 = 120ms, nível 10 = 12ms)
        const intervalMs = Math.max(10, Math.round(120 / (scrollSpeedLevel * 0.9)));
        autoScrollTimer = setInterval(() => {
            window.scrollBy({ top: 1, behavior: 'instant' });
            if ((window.scrollY + window.innerHeight) >= document.body.scrollHeight - 10) {
                stopAutoScroll();
            }
        }, intervalMs);
    }

    function stopAutoScroll() {
        clearInterval(autoScrollTimer);
        autoScrollTimer = null;

        const btn = document.getElementById('btn-autoroll');
        const label = document.getElementById('btn-autoroll-label');

        if (btn) {
            btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
            btn.classList.add('bg-slate-50', 'hover:bg-emerald-50', 'text-slate-600', 'hover:text-emerald-700', 'border-slate-200');
        }
        if (label) label.textContent = 'Iniciar Autorolagem';
    }

    function updateScrollSpeed(val) {
        scrollSpeedLevel = parseInt(val);
        const lbl = document.getElementById('scroll-speed-label');
        if (lbl) lbl.textContent = val + 'x';

        // Reinicia se estiver rodando
        if (autoScrollTimer) {
            stopAutoScroll();
            startAutoScroll();
        }
    }

    // ──────────────────────────────────────────────────────
    // Modo Exibição (Cifra / Apenas Letra)
    // ──────────────────────────────────────────────────────
    function setModoExibicao(modo) {
        modoExibicaoAtual = modo;
        const ativo = 'px-3 py-1.5 rounded-xl bg-purple-600 text-white font-extrabold text-xs shadow-xs transition active:scale-95';
        const inativo = 'px-3 py-1.5 rounded-xl bg-transparent text-purple-700 hover:bg-purple-100 font-bold text-xs transition active:scale-95';

        document.getElementById('btn-modo-cifra').className = modo === 'cifra' ? ativo : inativo;
        document.getElementById('btn-modo-letra').className = modo === 'letra' ? ativo : inativo;

        document.querySelectorAll('[id^="painel-tom-"]').forEach(p => {
            p.style.display = modo === 'letra' ? 'none' : '';
        });

        MUSICA_IDS.forEach(id => aplicarTransposicao(id));
    }

    // ──────────────────────────────────────────────────────
    // Tamanho de Fonte
    // ──────────────────────────────────────────────────────
    function alterarFonte(delta) {
        currentFontSize = Math.max(10, Math.min(24, currentFontSize + delta));
        document.querySelectorAll('.cifra-box').forEach(el => {
            el.style.fontSize = currentFontSize + 'px';
        });
    }

    // ──────────────────────────────────────────────────────
    // Transposição Cromática
    // ──────────────────────────────────────────────────────
    const chromaticSharps = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
    const flatToSharpMap = { Db: 'C#', Eb: 'D#', Gb: 'F#', Ab: 'G#', Bb: 'A#', Cb: 'B', Fb: 'E' };

    function normalizeNote(n) { return flatToSharpMap[n] || n; }
    function transposeNote(note, st) {
        const i = chromaticSharps.indexOf(normalizeNote(note));
        if (i === -1) return note;
        return chromaticSharps[((i + st) % 12 + 12) % 12];
    }
    function transposeChordToken(chord, st) {
        // Transpõe raiz e nota de baixo (ex: G/B)
        return chord.replace(/[A-G][#b]?/g, m => transposeNote(m, st));
    }

    function isChordLine(line) {
        const trimmed = line.trim();
        if (!trimmed) return false;

        // Detecta linha de tablatura (ex: e|---0---3-, B|--1----, etc.)
        if (/^[eEBGDAd]\|/.test(trimmed) || /^\|[-0-9hpbr\/\\|]+\|?/.test(trimmed)) return true;
        // Detecta linha composta apenas de traços, números e separadores (tabs puras)
        if (/^[-|0-9hpbr\/\\\s]+$/.test(trimmed) && /[\-]{2,}/.test(trimmed) && !/[a-zA-Z]{2,}/.test(trimmed)) return true;

        const words = trimmed.split(/\s+/);
        // Acorde: começa com A-G, pode ter #/b, sufixo e inversão
        const chordRe = /^[A-G][#b]?(m|maj|min|dim|aug|sus|add|M|[0-9]|\+|\-|\(|\))*(\/[A-G][#b]?)?$/;
        const matched = words.filter(w => chordRe.test(w.replace(/^[\[\(]|[\]\)]$/g, '')));
        return matched.length / words.length >= 0.5;
    }

    function transporMusica(id, delta) {
        musicSemitoneOffsets[id] = (musicSemitoneOffsets[id] || 0) + delta;
        aplicarTransposicao(id);
    }
    function resetarTomMusica(id) {
        musicSemitoneOffsets[id] = 0;
        aplicarTransposicao(id);
    }

    function aplicarTransposicao(id) {
        const offset = musicSemitoneOffsets[id] || 0;
        const preElem = document.getElementById('cifra-pre-' + id);
        const tomElem = document.getElementById('tom-display-' + id);
        if (!preElem) return;

        const original = preElem.getAttribute('data-original-text') || '';
        const origTom = tomElem ? (tomElem.getAttribute('data-original-tom') || 'C') : 'C';
        if (tomElem) tomElem.textContent = transposeNote(origTom, offset);

        const lines = original.split('\n');
        const chordTokenRe = /[A-G][#b]?(m|maj|min|dim|aug|sus|add|M|[0-9]|\+|\-|\(|\))*(\/[A-G][#b]?)?/g;

        let processed;
        if (modoExibicaoAtual === 'letra') {
            // Remove linhas de acordes — mantém apenas linhas de letra
            processed = lines.filter(l => !isChordLine(l));
        } else {
            processed = lines.map(l => {
                if (offset !== 0 && isChordLine(l)) {
                    return l.replace(chordTokenRe, m => transposeChordToken(m, offset));
                }
                return l;
            });
        }

        preElem.textContent = processed.join('\n');
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>