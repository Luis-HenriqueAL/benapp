<?php
/**
 * View: Visualizador Responsivo de Cifras e Letras (Cifra Club com Transposição)
 * 
 * Interface para músicos e membros acompanharem as cifras e letras
 * selecionadas pelo líder para o encontro de célula.
 * 
 * @var array $musicas Lista de músicas com cifras vinculadas ao evento.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

ob_start(); 
?>
<div class="space-y-5 max-w-2xl mx-auto pb-10">
    <!-- Header do Visualizador -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center justify-between">
        <div class="flex items-center space-x-3.5">
            <a href="javascript:history.back()" class="p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 text-slate-600 hover:text-purple-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0" aria-label="Voltar para a escala">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>🎵 Cifras & Letras</span>
                </h2>
                <p class="text-xs text-slate-400 font-medium">Louvor do Encontro</p>
            </div>
        </div>
        
        <!-- Controles de Tamanho de Fonte -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl border border-slate-200">
            <button type="button" onclick="alterarFonte(-1)" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-black text-xs shadow-xs transition active:scale-90 flex items-center justify-center" title="Diminuir fonte">A-</button>
            <button type="button" onclick="alterarFonte(1)" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-black text-xs shadow-xs transition active:scale-90 flex items-center justify-center" title="Aumentar fonte">A+</button>
        </div>
    </div>

    <?php if (empty($musicas)): ?>
        <div class="bg-white rounded-3xl p-10 text-center space-y-3 border border-slate-100 shadow-sm">
            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-3xl flex items-center justify-center mx-auto text-2xl font-bold">🎵</div>
            <h3 class="text-base font-extrabold text-slate-800">Nenhuma cifra cadastrada</h3>
            <p class="text-xs text-slate-400 max-w-xs mx-auto">Nenhuma música com cifra foi selecionada para este encontro ainda.</p>
            <div class="pt-2">
                <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-purple-500/20 transition active:scale-95">
                    Voltar para a Escala
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($musicas as $index => $m): ?>
                <?php 
                    $musicaId = (int)$m['id']; 
                    $tomOriginal = !empty($m['tom']) ? SecurityHelper::e($m['tom']) : 'C';
                ?>
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden space-y-4 p-5">
                    <!-- Cabeçalho da Música -->
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="space-y-1 min-w-0">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 bg-purple-50 border border-purple-100 px-2 py-0.5 rounded-lg">
                                Música #<?= $index + 1 ?>
                            </span>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight leading-snug truncate">
                                <?= SecurityHelper::e($m['titulo']) ?>
                            </h3>
                            <?php if (!empty($m['artista'])): ?>
                                <p class="text-xs font-bold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <?= SecurityHelper::e($m['artista']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <?php if (!empty($m['cifraclub_url'])): ?>
                                <a href="<?= SecurityHelper::e($m['cifraclub_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-[10px] font-bold text-purple-600 hover:text-purple-800 flex items-center gap-1 hover:underline">
                                    Ver no Cifra Club
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Painel Interativo de Transposição de Tom -->
                    <?php if (!empty($m['cifra_texto'])): ?>
                        <div class="bg-amber-50/70 border border-amber-200/80 p-3.5 rounded-2xl flex flex-wrap items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-extrabold text-amber-900 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 .895-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 .895-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                                    Tom Atual:
                                </span>
                                <span id="tom-display-<?= $musicaId ?>" class="px-2.5 py-1 rounded-xl bg-amber-600 text-white font-black text-xs tracking-wide shadow-xs" data-original-tom="<?= $tomOriginal ?>">
                                    <?= $tomOriginal ?>
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="transporMusica(<?= $musicaId ?>, -1)" class="px-3 py-1.5 rounded-xl bg-white hover:bg-amber-100 border border-amber-300 text-amber-900 font-extrabold text-xs shadow-xs active:scale-95 transition flex items-center gap-1">
                                    -1 Semi
                                </button>
                                <button type="button" onclick="transporMusica(<?= $musicaId ?>, 1)" class="px-3 py-1.5 rounded-xl bg-white hover:bg-amber-100 border border-amber-300 text-amber-900 font-extrabold text-xs shadow-xs active:scale-95 transition flex items-center gap-1">
                                    +1 Semi
                                </button>
                                <button type="button" onclick="resetarTomMusica(<?= $musicaId ?>)" class="px-2.5 py-1.5 rounded-xl bg-amber-200/60 hover:bg-amber-200 text-amber-950 font-bold text-[10px] active:scale-95 transition" title="Restaurar Tom Original">
                                    Resetar
                                </button>
                            </div>
                        </div>

                        <!-- Bloco da Cifra Monospaced -->
                        <div class="relative">
                            <pre id="cifra-pre-<?= $musicaId ?>" data-original-text="<?= SecurityHelper::e($m['cifra_texto']) ?>" class="cifra-box font-mono text-xs md:text-sm leading-relaxed overflow-x-auto p-4 bg-slate-900 text-emerald-400 rounded-2xl shadow-inner border border-slate-800 selection:bg-purple-600 selection:text-white whitespace-pre font-bold tracking-wider"><?= SecurityHelper::e($m['cifra_texto']) ?></pre>
                        </div>
                    <?php else: ?>
                        <div class="bg-slate-50 rounded-2xl p-6 text-center space-y-3 border border-slate-100">
                            <p class="text-xs text-slate-500 font-medium">
                                A cifra ainda não foi armazenada em cache para esta música.
                            </p>
                            <?php if (!empty($m['cifraclub_url'])): ?>
                                <a href="?id=<?= $musicaId ?>&force_refresh=1" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-xs transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span>Buscar / Atualizar Cifra no Cifra Club</span>
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
// Controle de Tamanho de Fonte
let currentFontSize = 13;
function alterarFonte(delta) {
    currentFontSize = Math.max(10, Math.min(22, currentFontSize + delta));
    const elements = document.querySelectorAll('.cifra-box');
    elements.forEach(el => {
        el.style.fontSize = currentFontSize + 'px';
    });
}

// Motor de Transposição Cromática de Cifras
const chromaticSharps = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

const flatToSharpMap = {
    'Db': 'C#', 'Eb': 'D#', 'Gb': 'F#', 'Ab': 'G#', 'Bb': 'A#', 'Cb': 'B', 'Fb': 'E'
};

const musicSemitoneOffsets = {};

function normalizeNoteName(note) {
    return flatToSharpMap[note] || note;
}

function transposeNoteName(note, semitones) {
    const norm = normalizeNoteName(note);
    const index = chromaticSharps.indexOf(norm);
    if (index === -1) return note;
    let newIndex = (index + semitones) % 12;
    if (newIndex < 0) newIndex += 12;
    return chromaticSharps[newIndex];
}

function transposeChordToken(chord, semitones) {
    // Substitui cada nota (raiz e nota de baixo após barra e.g. G/B -> A/C#)
    return chord.replace(/([A-G][#b]?)/g, function(match) {
        return transposeNoteName(match, semitones);
    });
}

function transporMusica(id, delta) {
    if (!musicSemitoneOffsets[id]) {
        musicSemitoneOffsets[id] = 0;
    }
    musicSemitoneOffsets[id] += delta;
    aplicarTransposicao(id);
}

function resetarTomMusica(id) {
    musicSemitoneOffsets[id] = 0;
    aplicarTransposicao(id);
}

function isChordLine(line) {
    const trimmed = line.trim();
    if (!trimmed) return false;

    const words = trimmed.split(/\s+/);
    if (words.length === 0) return false;

    // Testador de sintaxe de acorde (ex: C, C#m, G/B, F#m7(9), Bb7+)
    const chordPattern = /^[A-G][#b]?(m|maj|min|dim|aug|sus|add|M|[0-9]|\+|\-|\(|\))*(\/[A-G][#b]?)?$/i;

    let chordCount = 0;
    for (let word of words) {
        const cleanWord = word.replace(/^[\[\(]/, '').replace(/[\]\)]$/, '');
        if (chordPattern.test(cleanWord)) {
            chordCount++;
        }
    }

    return (chordCount / words.length) >= 0.5;
}

function aplicarTransposicao(id) {
    const offset = musicSemitoneOffsets[id] || 0;
    const preElem = document.getElementById('cifra-pre-' + id);
    const tomElem = document.getElementById('tom-display-' + id);

    if (!preElem) return;

    const originalText = preElem.getAttribute('data-original-text') || '';
    const originalTom  = tomElem ? (tomElem.getAttribute('data-original-tom') || 'C') : 'C';

    // Transpõe a nota do Tom no cabeçalho
    if (tomElem) {
        tomElem.textContent = transposeNoteName(originalTom, offset);
    }

    if (offset === 0) {
        preElem.textContent = originalText;
        return;
    }

    // Processa linha por linha aplicando transposição em linhas de acordes
    const lines = originalText.split('\n');
    const chordTokenRegex = /([A-G][#b]?)(m|maj|min|dim|aug|sus|add|M|[0-9]|\+|\-|\(|\))*(\/([A-G][#b]?))?/g;

    const transposedLines = lines.map(line => {
        if (isChordLine(line)) {
            return line.replace(chordTokenRegex, function(match) {
                // Transpõe a raiz e a nota de baixo do acorde
                return transposeChordToken(match, offset);
            });
        }
        return line;
    });

    preElem.textContent = transposedLines.join('\n');
}
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
