<?php
/**
 * View: Visualização Detalhada do Evento / Escala
 * 
 * Exibe as informações completas de um culto ou evento cadastrado:
 * Data, nome do evento, dados da célula (endereço e anfitriões) e a sequência de momentos da liturgia
 * com os voluntários alocados.
 * 
 * @var array $liturgia Dados do culto e lista de atribuições.
 * @var array|null $celulaInfo Informações cadastrais da célula.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

$dataCultoFormatted = !empty($liturgia['data_culto']) ? date('d/m/Y', strtotime($liturgia['data_culto'])) : 'Data a definir';
$tema = !empty($liturgia['tema']) ? $liturgia['tema'] : 'Encontro de Célula';
$atribuicoes = $liturgia['atribuicoes'] ?? [];

$nomeCelula = !empty($celulaInfo['nome']) ? $celulaInfo['nome'] : (!empty($celulaInfo['nome_celula']) ? $celulaInfo['nome_celula'] : 'Célula Boas Novas');
$horarioCelula = !empty($celulaInfo['horario']) ? substr($celulaInfo['horario'], 0, 5) : '';
$isVisitorMode = isset($_SESSION['visitante']) && !isset($_SESSION['user']);

$lideresNomes = [];
if (!empty($celulaInfo['lideres']) && is_array($celulaInfo['lideres'])) {
    foreach ($celulaInfo['lideres'] as $lid) {
        $n = trim($lid['nome'] ?? '');
        if ($n !== '') {
            $lideresNomes[] = SecurityHelper::e($n);
        }
    }
}
$strLideres = implode(', ', $lideresNomes);

$anfitrioesNomes = [];
if (!empty($celulaInfo['anfitrioes']) && is_array($celulaInfo['anfitrioes'])) {
    foreach ($celulaInfo['anfitrioes'] as $anf) {
        $n = trim($anf['nome'] ?? '');
        if ($n !== '') {
            $anfitrioesNomes[] = SecurityHelper::e($n);
        }
    }
}
$strAnfitrioes = implode(', ', $anfitrioesNomes);

ob_start();
?>
<div class="space-y-5 max-w-md mx-auto pb-6">
    <?php if ($isVisitorMode): ?>
        <div
            class="bg-gradient-to-r from-purple-700 via-indigo-700 to-blue-700 text-white rounded-3xl p-4 shadow-lg border border-purple-400/30 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center font-extrabold text-lg">
                    🎟️
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-purple-200">Acesso Visitante</p>
                    <p class="text-sm font-black">
                        <?= SecurityHelper::e($_SESSION['visitante']['nome'] ?? 'Visitante') ?>
                    </p>
                </div>
            </div>
            <a href="/visitante/sair"
                class="px-3.5 py-2 bg-white/15 hover:bg-white/25 active:scale-95 text-white font-extrabold text-xs rounded-xl transition border border-white/20 backdrop-blur-md">
                Sair
            </a>
        </div>
    <?php endif; ?>

    <!-- Header da Seção -->
    <div
        class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex justify-between items-center">
        <div class="flex items-center space-x-3.5 min-w-0">
            <a href="<?= $isVisitorMode ? '/visitante/sair' : '/escala' ?>"
                class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0"
                aria-label="Voltar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div class="min-w-0">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight truncate">Detalhes da Escala</h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5 truncate">Programação e voluntários escalados</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <?php if (SecurityHelper::hasPermissao('escala.create')): ?>
                <a href="/escala/edit?id=<?= (int) $liturgia['id'] ?>"
                    class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-blue-600 border border-slate-100 active:scale-90 transition-all shadow-xs flex items-center justify-center"
                    aria-label="Editar evento" title="Editar Liturgia/Escala">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                </a>
            <?php endif; ?>
            <?php if (SecurityHelper::hasPermissao('escala.delete')): ?>
                <form action="/escala/delete" method="POST"
                    data-confirm="Tem certeza que deseja excluir este evento? Esta ação é irreversível.">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int) $liturgia['id'] ?>">
                    <button type="submit"
                        class="p-2.5 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 active:scale-90 transition-all shadow-xs flex items-center justify-center"
                        aria-label="Excluir evento" title="Excluir evento">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Header do Evento -->
    <div
        class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-700 rounded-3xl p-6 text-white shadow-xl shadow-blue-500/20 space-y-3">
        <div class="flex justify-between items-start">
            <span
                class="px-3 py-1 bg-white/20 backdrop-blur-md border border-white/30 text-[10px] font-extrabold uppercase tracking-wider rounded-full">
                <?= SecurityHelper::e($tema) ?>
            </span>
            <span class="text-xs font-bold text-blue-100 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <?= SecurityHelper::e($horarioCelula) ?>h
            </span>
        </div>

        <div>
            <h1 class="text-2xl font-black tracking-tight leading-snug">
                <?= SecurityHelper::e($dataCultoFormatted) ?>
            </h1>
            <p class="text-xs text-blue-100 font-medium mt-1">Célula: <strong>
                    <?= SecurityHelper::e($nomeCelula) ?>
                </strong></p>
            <?php if (!empty($strLideres)): ?>
                <p class="text-xs text-blue-100/90 font-medium mt-0.5">Líder(es): <strong class="text-white">
                        <?= $strLideres ?>
                    </strong></p>
            <?php endif; ?>
            <?php if (!empty($strAnfitrioes)): ?>
                <p class="text-xs text-blue-100/90 font-medium mt-0.5">Anfitrião(ões): <strong class="text-white">
                        <?= $strAnfitrioes ?>
                    </strong></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($celulaInfo['logradouro'])): ?>
            <?php
            $enderecoFormatado = SecurityHelper::e($celulaInfo['logradouro']) . ', ' .
                SecurityHelper::e($celulaInfo['numero'] ?? 'S/N') .
                (!empty($celulaInfo['bairro']) ? ' - ' . SecurityHelper::e($celulaInfo['bairro']) : '') .
                (!empty($celulaInfo['cidade']) ? ', ' . SecurityHelper::e($celulaInfo['cidade']) : '') .
                (!empty($celulaInfo['estado']) ? ' - ' . SecurityHelper::e($celulaInfo['estado']) : '');
            ?>
            <button type="button" onclick="abrirModalMapa('<?= SecurityHelper::e($enderecoFormatado) ?>')"
                class="pt-3 border-t border-white/15 flex items-start space-x-2 text-xs text-blue-100 hover:text-white transition-all group/addr text-left w-full cursor-pointer"
                title="Clique para ver o endereço no mapa">
                <svg class="w-4 h-4 text-blue-200 group-hover/addr:text-white transition-colors mt-0.5 shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span
                    class="underline underline-offset-2 decoration-blue-300/40 group-hover/addr:decoration-white font-medium flex-1">
                    <?= SecurityHelper::e($celulaInfo['logradouro']) ?>,
                    <?= SecurityHelper::e($celulaInfo['numero'] ?? 'S/N') ?>
                    <?= !empty($celulaInfo['bairro']) ? ' - ' . SecurityHelper::e($celulaInfo['bairro']) : '' ?>
                    <span
                        class="ml-1 text-[10px] bg-white/20 hover:bg-white/30 text-white px-2 py-0.5 rounded-lg font-bold inline-flex items-center gap-1 transition shadow-xs">
                        🗺️ Ver Mapa
                    </span>
                </span>
            </button>
        <?php endif; ?>
    </div>

    <!-- Linha do Tempo da Liturgia e Voluntários -->
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 space-y-4">
        <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
            <div
                class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                </svg>
            </div>
            <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Liturgia & Responsáveis</h3>
        </div>

        <?php if (empty($atribuicoes)): ?>
            <div class="p-6 text-center text-slate-400 text-xs font-semibold">
                Nenhum momento ou voluntário cadastrado nesta liturgia.
            </div>
        <?php else: ?>
            <div class="space-y-3.5">
                <?php foreach ($atribuicoes as $idx => $att): ?>
                    <div class="flex items-start space-x-3.5 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div
                            class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-xs">
                            #
                            <?= $idx + 1 ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-extrabold text-slate-900 leading-snug truncate">
                                <?= SecurityHelper::e($att['funcao_id'] ?? 'Momento Litúrgico') ?>
                            </h4>
                            <p class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                Responsável: <strong class="text-slate-800">
                                    <?= SecurityHelper::e($att['voluntario_nome'] ?? 'Voluntário Alocado') ?>
                                </strong>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Seção de Louvor & Cifras da Liturgia -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xl shadow-slate-200/40 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight flex items-center gap-1.5">
                    <span>🎵 Repertório & Cifras de Louvor</span>
                </h3>
                <p class="text-[10px] text-slate-400 font-medium">
                    <?= count($musicas ?? []) ?> música(s) vinculada(s)
                </p>
            </div>
            <?php if (!empty($musicas)): ?>
                <a href="/escala/cifra?liturgia_id=<?= (int) $liturgia['id'] ?>"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs transition-all shadow-md shadow-purple-500/20">
                    <span>Ver Cifras</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3">
                        </path>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <?php if (SecurityHelper::hasPermissao('escala.edit')): ?>
            <div class="pt-1">
                <button type="button" onclick="toggleForm('formNovaMusica')"
                    class="w-full py-2.5 px-4 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-2xl text-xs font-bold transition-all active:scale-95 flex items-center justify-center gap-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Adicionar Música (Cifra Club)
                </button>
            </div>

            <!-- Formulário para Inserir Música -->
            <div id="formNovaMusica" class="hidden bg-purple-50/70 rounded-2xl p-4 border border-purple-100 space-y-3">
                <h4 class="text-xs font-extrabold text-purple-900">Vincular Nova Música</h4>
                <form action="/escala/musica/adicionar" method="POST" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int) $liturgia['id'] ?>">

                    <div>
                        <label for="cifraclub_url" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Link
                            do Cifra Club (Recomendado)</label>
                        <input id="cifraclub_url" type="url" name="cifraclub_url"
                            placeholder="Ex: https://www.cifraclub.com.br/artista/musica/"
                            class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-600">
                        <p class="text-[10px] text-purple-600/80 mt-1">Cole o link da cifra para buscar a letra e os acordes
                            automaticamente.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="titulo_musica"
                                class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Título da Música</label>
                            <input id="titulo_musica" type="text" name="titulo" placeholder="Ex: Lugar Secreto"
                                class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>
                        <div>
                            <label for="artista_musica"
                                class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Artista / Banda</label>
                            <input id="artista_musica" type="text" name="artista" placeholder="Ex: Gabriela Rocha"
                                class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>
                    </div>

                    <div>
                        <label for="tom_musica" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Tom da
                            Música</label>
                        <input id="tom_musica" type="text" name="tom" placeholder="Ex: G, C#, Fm"
                            class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div class="flex gap-2 justify-end pt-1">
                        <button type="button" onclick="toggleForm('formNovaMusica')"
                            class="px-3 py-2 text-xs font-bold text-purple-600 hover:text-purple-800">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-xs">Salvar
                            Música</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Lista de Músicas Vinculadas -->
        <?php if (!empty($musicas)): ?>
            <div class="space-y-2">
                <?php foreach ($musicas as $mus): ?>
                    <div
                        class="flex items-center justify-between p-3 rounded-2xl bg-purple-50/50 border border-purple-100 hover:bg-purple-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 font-bold text-sm flex items-center justify-center shrink-0">
                                🎵
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-extrabold text-slate-900 truncate">
                                    <?= SecurityHelper::e($mus['titulo']) ?>
                                </h4>
                                <p class="text-[10px] text-slate-400 font-medium truncate">
                                    <?= SecurityHelper::e($mus['artista'] ?? 'Artista não informado') ?>
                                    <?php if (!empty($mus['tom'])): ?>
                                        • <span class="font-extrabold text-purple-700">Tom:
                                            <?= SecurityHelper::e($mus['tom']) ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="/escala/cifra?id=<?= $mus['id'] ?>"
                                class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition active:scale-95 shadow-xs">
                                Ver Cifra
                            </a>

                            <?php if (SecurityHelper::hasPermissao('escala.edit')): ?>
                                <form action="/escala/musica/remover" method="POST"
                                    data-confirm="Deseja remover esta música da liturgia?">
                                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                                    <input type="hidden" name="liturgia_id" value="<?= (int) $liturgia['id'] ?>">
                                    <input type="hidden" name="id" value="<?= (int) $mus['id'] ?>">
                                    <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                        title="Remover música">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-xs text-slate-400 text-center py-2 italic">Nenhuma música adicionada ao louvor ainda.</p>
        <?php endif; ?>
    </div>

    <!-- Seção de Presenças e Visitantes -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xl shadow-slate-200/40 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Confirmados</h3>
                <p class="text-[10px] text-slate-400 font-medium">
                    <?= count($presencas ?? []) ?> pessoa(s) confirmada(s)
                </p>
            </div>
            <?php if (!$isVisitorMode): ?>
                <?php if ($usuarioLogadoConfirmado ?? false): ?>
                    <form action="/presenca/cancelar" method="POST" data-confirm="Deseja cancelar sua confirmação de presença?">
                        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                        <input type="hidden" name="liturgia_id" value="<?= (int) $liturgia['id'] ?>">
                        <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-extrabold text-xs transition-all hover:bg-emerald-100 active:scale-95">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Confirmado
                        </button>
                    </form>
                <?php else: ?>
                    <form action="/presenca/confirmar" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                        <input type="hidden" name="liturgia_id" value="<?= (int) $liturgia['id'] ?>">
                        <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-extrabold text-xs transition-all shadow-md shadow-blue-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Eu Vou
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if (!$isVisitorMode): ?>
            <!-- Botões de Ação Secundária (Outro Membro / Visitante) -->
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleForm('formOutroMembro')"
                    class="flex-1 py-2.5 px-3 bg-slate-50 hover:bg-blue-50 text-blue-600 border border-slate-200 rounded-2xl text-xs font-bold transition-all active:scale-95 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    Confirmar Membro
                </button>
                <button type="button" onclick="toggleForm('formVisitante')"
                    class="flex-1 py-2.5 px-3 bg-slate-50 hover:bg-purple-50 text-purple-600 border border-slate-200 rounded-2xl text-xs font-bold transition-all active:scale-95 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Novo Visitante
                </button>
            </div>

            <!-- Formulário para Confirmar Outro Membro -->
            <div id="formOutroMembro" class="hidden bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-3">
                <h4 class="text-xs font-extrabold text-slate-800">Confirmar Presença de Membro</h4>
                <form action="/presenca/confirmar" method="POST" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int) $liturgia['id'] ?>">
                    <div>
                        <label for="usuario_id_select"
                            class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Selecione o Membro</label>
                        <select id="usuario_id_select" name="usuario_id" required
                            class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="">-- Escolha um membro da equipe --</option>
                            <?php foreach ($todosUsuarios as $u): ?>
                                <?php
                                $jaConf = array_filter($presencas ?? [], fn($p) => (($p['usuario_id'] ?? 0) == $u['id']));
                                ?>
                                <option value="<?= $u['id'] ?>" <?= !empty($jaConf) ? 'disabled' : '' ?>>
                                    <?= SecurityHelper::e($u['nome']) ?> (
                                    <?= SecurityHelper::e($u['perfil']) ?>)
                                    <?= !empty($jaConf) ? '- Já Confirmado' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="toggleForm('formOutroMembro')"
                            class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-xs">Confirmar
                            Membro</button>
                    </div>
                </form>
            </div>

            <!-- Formulário para Adicionar Visitante -->
            <div id="formVisitante" class="hidden bg-purple-50/60 rounded-2xl p-4 border border-purple-100 space-y-3">
                <h4 class="text-xs font-extrabold text-purple-900">Cadastrar Novo Visitante</h4>
                <form action="/presenca/visitante" method="POST" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int) $liturgia['id'] ?>">
                    <div>
                        <label for="nome_visitante" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Nome
                            do Visitante</label>
                        <input id="nome_visitante" type="text" name="nome_visitante" list="visitantes_historico" required
                            placeholder="Ex: João da Silva" oninput="atualizarQtdVisitaVisitante(this.value)"
                            class="w-full px-3 py-2.5 bg-white border border-purple-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-purple-600">
                        <datalist id="visitantes_historico">
                            <?php foreach ($visitantesHistorico ?? [] as $vh): ?>
                                <option value="<?= SecurityHelper::e($vh['nome_visitante']) ?>"
                                    data-proxima-visita="<?= (int) ($vh['max_visitas'] + 1) ?>">
                                    Visitante anterior (já foi
                                    <?= (int) $vh['max_visitas'] ?>x)
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label for="qtd_visitas" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Vezes
                            que já foi à célula</label>
                        <input id="qtd_visitas" type="number" name="qtd_visitas" value="1" min="1" required
                            class="w-full px-3 py-2.5 bg-white border border-purple-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="toggleForm('formVisitante')"
                            class="px-3 py-2 text-xs font-bold text-purple-600 hover:text-purple-800">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-xs">Cadastrar
                            Visitante</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Lista de Confirmados (Membros + Visitantes) -->
        <?php if (!empty($presencas)): ?>
            <?php
            $loggedUserId = (int) ($_SESSION['user']['id'] ?? 0);
            $canDeleteAny = SecurityHelper::hasPermissao('escala.delete');
            ?>
            <div class="space-y-2 pt-1">
                <?php foreach ($presencas as $p): ?>
                    <?php
                    $isVisitante = ($p['tipo'] ?? '') === 'visitante' || empty($p['usuario_id']);
                    $podeRemover = $canDeleteAny ||
                        (!empty($p['usuario_id']) && (int) $p['usuario_id'] === $loggedUserId) ||
                        (!empty($p['registrado_por_id']) && (int) $p['registrado_por_id'] === $loggedUserId);
                    ?>
                    <div
                        class="flex items-center gap-3 p-3 rounded-2xl <?= $isVisitante ? 'bg-purple-50/60 border border-purple-100' : 'bg-emerald-50/60 border border-emerald-100' ?>">
                        <div
                            class="w-8 h-8 rounded-xl <?= $isVisitante ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' ?> font-extrabold text-sm flex items-center justify-center shrink-0">
                            <?= strtoupper(substr($p['usuario_nome'] ?? '?', 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 truncate">
                                <?= SecurityHelper::e($p['usuario_nome'] ?? '') ?>
                            </p>
                            <?php if ($isVisitante): ?>
                                <p class="text-[10px] text-purple-600 font-extrabold tracking-wide uppercase">
                                    Visitante •
                                    <?= (int) ($p['qtd_visitas'] ?? 1) ?>ª visita à célula
                                </p>
                                <?php if (!empty($p['codigo_acesso'])): ?>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        <span
                                            class="px-2 py-0.5 rounded-lg bg-purple-100/90 border border-purple-200 text-purple-800 text-[10px] font-black tracking-widest font-mono">
                                            🎟️
                                            <?= SecurityHelper::e($p['codigo_acesso']) ?>
                                        </span>
                                        <button type="button"
                                            onclick="navigator.clipboard.writeText('<?= SecurityHelper::e($p['codigo_acesso']) ?>'); alert('Código <?= SecurityHelper::e($p['codigo_acesso']) ?> copiado!')"
                                            class="text-[10px] font-bold text-purple-700 hover:text-purple-900 underline">
                                            Copiar
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">
                                    <?= SecurityHelper::e($p['usuario_perfil'] ?? 'MEMBRO') ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php if ($podeRemover): ?>
                            <form action="/presenca/cancelar" method="POST" data-confirm="Remover esta confirmação de presença?">
                                <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                                <input type="hidden" name="liturgia_id" value="<?= (int) $liturgia['id'] ?>">
                                <input type="hidden" name="presenca_id" value="<?= (int) $p['id'] ?>">
                                <button type="submit"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                    title="Remover presença">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-xs text-slate-400 text-center py-2 italic">Nenhuma confirmação de presença ainda.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Leaflet.js (OpenStreetMap Interactive Maps) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<!-- Modal de Mapa (OpenStreetMap) -->
<div id="modal-mapa"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/70 backdrop-blur-md transition-all duration-300">
    <div
        class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-200">
        <!-- Header do Modal -->
        <div
            class="px-5 py-4 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-9 h-9 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center font-extrabold text-base shadow-inner">
                    🗺️
                </div>
                <div>
                    <h3 class="font-black text-sm tracking-tight leading-tight">Localização da Célula</h3>
                    <p class="text-[10px] text-blue-100 font-extrabold uppercase tracking-wider">OpenStreetMap</p>
                </div>
            </div>
            <button type="button" onclick="fecharModalMapa()"
                class="p-2 rounded-2xl bg-white/10 hover:bg-white/20 text-white transition active:scale-90 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Corpo do Modal -->
        <div class="p-4 space-y-3.5 overflow-y-auto">
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 flex items-start gap-2.5">
                <div class="p-1.5 bg-blue-100 text-blue-700 rounded-xl shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Endereço Completo</p>
                    <p id="modal-endereco-conteudo"
                        class="text-xs font-bold text-slate-800 leading-snug break-words mt-0.5">Carregando...</p>
                </div>
            </div>

            <!-- Container do Mapa Leaflet -->
            <div
                class="relative w-full h-72 rounded-2xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
                <div id="osm-map-container" class="w-full h-full z-10"></div>
                <div id="map-loading"
                    class="absolute inset-0 bg-slate-50/90 z-20 flex flex-col items-center justify-center gap-2 text-slate-500">
                    <svg class="w-7 h-7 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-xs font-extrabold text-slate-700">Localizando no OpenStreetMap...</span>
                </div>
            </div>
        </div>

        <!-- Rodapé de Ações -->
        <div
            class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex flex-wrap gap-2 justify-between items-center">
            <button type="button" onclick="copiarEnderecoModal()"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-extrabold text-xs transition active:scale-95 flex items-center gap-1.5 shadow-xs">
                📋 Copiar Endereço
            </button>
            <div class="flex gap-2">
                <a id="btn-link-osm" href="#" target="_blank" rel="noopener noreferrer"
                    class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-extrabold text-xs transition active:scale-95 flex items-center gap-1.5">
                    🗺️ OpenStreetMap
                </a>
                <a id="btn-link-gmaps" href="#" target="_blank" rel="noopener noreferrer"
                    class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs transition active:scale-95 shadow-md shadow-blue-500/20 flex items-center gap-1.5">
                    🧭 Google Maps
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleForm(id) {
        const el = document.getElementById(id);
        if (el) {
            if (el.classList.contains('hidden')) {
                document.getElementById('formOutroMembro')?.classList.add('hidden');
                document.getElementById('formVisitante')?.classList.add('hidden');
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        }
    }

    function atualizarQtdVisitaVisitante(val) {
        const datalist = document.getElementById('visitantes_historico');
        const inputQtd = document.getElementById('qtd_visitas');
        if (!datalist || !inputQtd) return;

        for (let opt of datalist.options) {
            if (opt.value.trim().toLowerCase() === val.trim().toLowerCase()) {
                const proxVisita = opt.getAttribute('data-proxima-visita');
                if (proxVisita) {
                    inputQtd.value = proxVisita;
                }
                break;
            }
        }
    }

    // ──────────────────────────────────────────────────────
    // OpenStreetMap & Leaflet Integration
    // ──────────────────────────────────────────────────────
    let leafletMapInstance = null;
    let leafletMarker = null;

    function abrirModalMapa(endereco) {
        const modal = document.getElementById('modal-mapa');
        const conteudo = document.getElementById('modal-endereco-conteudo');
        const loading = document.getElementById('map-loading');
        const linkOsm = document.getElementById('btn-link-osm');
        const linkGmaps = document.getElementById('btn-link-gmaps');

        if (conteudo) conteudo.textContent = endereco;
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        if (loading) loading.style.display = 'flex';

        const gmapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(endereco)}`;
        if (linkGmaps) linkGmaps.href = gmapsUrl;

        const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(endereco)}`;

        fetch(nominatimUrl, { headers: { 'Accept-Language': 'pt-BR,pt;q=0.9' } })
            .then(res => res.json())
            .then(data => {
                let lat = -15.793889;
                let lon = -47.882778;
                if (data && data.length > 0) {
                    lat = parseFloat(data[0].lat);
                    lon = parseFloat(data[0].lon);
                }
                const osmUrl = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}#map=16/${lat}/${lon}`;
                if (linkOsm) linkOsm.href = osmUrl;

                renderizarMapaLeaflet(lat, lon, endereco);
            })
            .catch(err => {
                console.warn('Erro Nominatim OSM:', err);
                renderizarMapaLeaflet(-15.793889, -47.882778, endereco);
            })
            .finally(() => {
                if (loading) loading.style.display = 'none';
            });
    }

    function renderizarMapaLeaflet(lat, lon, label) {
        const container = document.getElementById('osm-map-container');
        if (!container) return;

        if (leafletMapInstance) {
            leafletMapInstance.remove();
            leafletMapInstance = null;
        }

        leafletMapInstance = L.map('osm-map-container', {
            zoomControl: true,
            attributionControl: false
        }).setView([lat, lon], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(leafletMapInstance);

        leafletMarker = L.marker([lat, lon]).addTo(leafletMapInstance);
        leafletMarker.bindPopup(`<div style="font-family:sans-serif; text-align:center; padding:2px;"><b>📍 Célula</b><br><span style="font-size:11px; color:#475569;">${label}</span></div>`).openPopup();

        setTimeout(() => {
            if (leafletMapInstance) leafletMapInstance.invalidateSize();
        }, 200);
    }

    function fecharModalMapa() {
        const modal = document.getElementById('modal-mapa');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function copiarEnderecoModal() {
        const conteudo = document.getElementById('modal-endereco-conteudo');
        if (conteudo && conteudo.textContent) {
            navigator.clipboard.writeText(conteudo.textContent);
            showCustomAlert('Endereço copiado para a área de transferência!', 'Copiado com Sucesso', 'sucesso');
        }
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') fecharModalMapa();
    });
    document.addEventListener('click', e => {
        const modal = document.getElementById('modal-mapa');
        if (e.target === modal) fecharModalMapa();
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>