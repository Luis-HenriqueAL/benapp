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
$horarioCelula = !empty($celulaInfo['horario']) ? substr($celulaInfo['horario'], 0, 5) : '19:30';

ob_start(); 
?>
<div class="space-y-5 max-w-md mx-auto pb-6">
    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex justify-between items-center">
        <div class="flex items-center space-x-3.5 min-w-0">
            <a href="/escala" class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0" aria-label="Voltar para escalas">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div class="min-w-0">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight truncate">Detalhes da Escala</h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5 truncate">Programação e voluntários escalados</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <?php if (SecurityHelper::hasPermissao('escala.create')): ?>
                <a href="/escala/edit?id=<?= (int)$liturgia['id'] ?>" class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-blue-600 border border-slate-100 active:scale-90 transition-all shadow-xs flex items-center justify-center" aria-label="Editar evento" title="Editar Liturgia/Escala">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </a>
            <?php endif; ?> 
            <?php if (SecurityHelper::hasPermissao('escala.delete')): ?>
                <form action="/escala/delete" method="POST" data-confirm="Tem certeza que deseja excluir este evento? Esta ação é irreversível.">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                    <button type="submit" class="p-2.5 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 active:scale-90 transition-all shadow-xs flex items-center justify-center" aria-label="Excluir evento" title="Excluir evento">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            <?php endif; ?> 
        </div> 
    </div>

    <!-- Header do Evento -->
    <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-700 rounded-3xl p-6 text-white shadow-xl shadow-blue-500/20 space-y-3">
        <div class="flex justify-between items-start">
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md border border-white/30 text-[10px] font-extrabold uppercase tracking-wider rounded-full">
                <?= SecurityHelper::e($tema) ?>
            </span>
            <span class="text-xs font-bold text-blue-100 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= SecurityHelper::e($horarioCelula) ?>h
            </span>
        </div>

        <div>
            <h1 class="text-2xl font-black tracking-tight leading-snug"><?= SecurityHelper::e($dataCultoFormatted) ?></h1>
            <p class="text-xs text-blue-100 font-medium mt-1">Célula: <strong><?= SecurityHelper::e($nomeCelula) ?></strong></p>
        </div>

        <?php if (!empty($celulaInfo['logradouro'])): ?>
            <div class="pt-3 border-t border-white/15 flex items-start space-x-2 text-xs text-blue-100">
                <svg class="w-4 h-4 text-blue-200 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>
                    <?= SecurityHelper::e($celulaInfo['logradouro']) ?>, <?= SecurityHelper::e($celulaInfo['numero'] ?? 'S/N') ?>
                    <?= !empty($celulaInfo['bairro']) ? ' - ' . SecurityHelper::e($celulaInfo['bairro']) : '' ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Linha do Tempo da Liturgia e Voluntários -->
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 space-y-4">
        <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
            <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
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
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-xs">
                            #<?= $idx + 1 ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-extrabold text-slate-900 leading-snug truncate">
                                <?= SecurityHelper::e($att['funcao_id'] ?? 'Momento Litúrgico') ?>
                            </h4>
                            <p class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                Responsável: <strong class="text-slate-800"><?= SecurityHelper::e($att['voluntario_nome'] ?? 'Voluntário Alocado') ?></strong>
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
                <p class="text-[10px] text-slate-400 font-medium"><?= count($musicas ?? []) ?> música(s) vinculada(s)</p>
            </div>
            <?php if (!empty($musicas)): ?>
                <a href="/escala/cifra?liturgia_id=<?= (int)$liturgia['id'] ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs transition-all shadow-md shadow-purple-500/20">
                    <span>Ver Cifras</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            <?php endif; ?>
        </div>

        <?php if (SecurityHelper::hasPermissao('escala.edit')): ?>
            <div class="pt-1">
                <button type="button" onclick="toggleForm('formNovaMusica')" class="w-full py-2.5 px-4 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-2xl text-xs font-bold transition-all active:scale-95 flex items-center justify-center gap-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Adicionar Música (Cifra Club)
                </button>
            </div>

            <!-- Formulário para Inserir Música -->
            <div id="formNovaMusica" class="hidden bg-purple-50/70 rounded-2xl p-4 border border-purple-100 space-y-3">
                <h4 class="text-xs font-extrabold text-purple-900">Vincular Nova Música</h4>
                <form action="/escala/musica/adicionar" method="POST" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                    
                    <div>
                        <label for="cifraclub_url" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Link do Cifra Club (Recomendado)</label>
                        <input id="cifraclub_url" type="url" name="cifraclub_url" placeholder="Ex: https://www.cifraclub.com.br/artista/musica/" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-600">
                        <p class="text-[10px] text-purple-600/80 mt-1">Cole o link da cifra para buscar a letra e os acordes automaticamente.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="titulo_musica" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Título da Música</label>
                            <input id="titulo_musica" type="text" name="titulo" placeholder="Ex: Lugar Secreto" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>
                        <div>
                            <label for="artista_musica" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Artista / Banda</label>
                            <input id="artista_musica" type="text" name="artista" placeholder="Ex: Gabriela Rocha" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>
                    </div>

                    <div>
                        <label for="tom_musica" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Tom da Música</label>
                        <input id="tom_musica" type="text" name="tom" placeholder="Ex: G, C#, Fm" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div class="flex gap-2 justify-end pt-1">
                        <button type="button" onclick="toggleForm('formNovaMusica')" class="px-3 py-2 text-xs font-bold text-purple-600 hover:text-purple-800">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-xs">Salvar Música</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Lista de Músicas Vinculadas -->
        <?php if (!empty($musicas)): ?>
            <div class="space-y-2">
                <?php foreach ($musicas as $mus): ?>
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-purple-50/50 border border-purple-100 hover:bg-purple-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 font-bold text-sm flex items-center justify-center shrink-0">
                                🎵
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-extrabold text-slate-900 truncate"><?= SecurityHelper::e($mus['titulo']) ?></h4>
                                <p class="text-[10px] text-slate-400 font-medium truncate">
                                    <?= SecurityHelper::e($mus['artista'] ?? 'Artista não informado') ?>
                                    <?php if (!empty($mus['tom'])): ?>
                                        • <span class="font-extrabold text-purple-700">Tom: <?= SecurityHelper::e($mus['tom']) ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="/escala/cifra?id=<?= $mus['id'] ?>" class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition active:scale-95 shadow-xs">
                                Ver Cifra
                            </a>

                            <?php if (SecurityHelper::hasPermissao('escala.edit')): ?>
                                <form action="/escala/musica/remover" method="POST" data-confirm="Deseja remover esta música da liturgia?">
                                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                                    <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                                    <input type="hidden" name="id" value="<?= (int)$mus['id'] ?>">
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Remover música">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                <p class="text-[10px] text-slate-400 font-medium"><?= count($presencas ?? []) ?> pessoa(s) confirmada(s)</p>
            </div>
            <?php if ($usuarioLogadoConfirmado ?? false): ?>
                <form action="/presenca/cancelar" method="POST" data-confirm="Deseja cancelar sua confirmação de presença?">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                    <button type="submit" class="flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-extrabold text-xs transition-all hover:bg-emerald-100 active:scale-95">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Confirmado
                    </button>
                </form>
            <?php else: ?>
                <form action="/presenca/confirmar" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                    <button type="submit" class="flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-extrabold text-xs transition-all shadow-md shadow-blue-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Eu Vou
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Botões de Ação Secundária (Outro Membro / Visitante) -->
        <div class="flex gap-2 pt-1">
            <button type="button" onclick="toggleForm('formOutroMembro')" class="flex-1 py-2.5 px-3 bg-slate-50 hover:bg-blue-50 text-blue-600 border border-slate-200 rounded-2xl text-xs font-bold transition-all active:scale-95 flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Confirmar Membro
            </button>
            <button type="button" onclick="toggleForm('formVisitante')" class="flex-1 py-2.5 px-3 bg-slate-50 hover:bg-purple-50 text-purple-600 border border-slate-200 rounded-2xl text-xs font-bold transition-all active:scale-95 flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Novo Visitante
            </button>
        </div>

        <!-- Formulário para Confirmar Outro Membro -->
        <div id="formOutroMembro" class="hidden bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-3">
            <h4 class="text-xs font-extrabold text-slate-800">Confirmar Presença de Membro</h4>
            <form action="/presenca/confirmar" method="POST" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                <div>
                    <label for="usuario_id_select" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Selecione o Membro</label>
                    <select id="usuario_id_select" name="usuario_id" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">-- Escolha um membro da equipe --</option>
                        <?php foreach ($todosUsuarios as $u): ?>
                            <?php 
                            $jaConf = array_filter($presencas ?? [], fn($p) => (($p['usuario_id'] ?? 0) == $u['id'])); 
                            ?>
                            <option value="<?= $u['id'] ?>" <?= !empty($jaConf) ? 'disabled' : '' ?>>
                                <?= SecurityHelper::e($u['nome']) ?> (<?= SecurityHelper::e($u['perfil']) ?>) <?= !empty($jaConf) ? '- Já Confirmado' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="toggleForm('formOutroMembro')" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-xs">Confirmar Membro</button>
                </div>
            </form>
        </div>

        <!-- Formulário para Adicionar Visitante -->
        <div id="formVisitante" class="hidden bg-purple-50/60 rounded-2xl p-4 border border-purple-100 space-y-3">
            <h4 class="text-xs font-extrabold text-purple-900">Cadastrar Novo Visitante</h4>
            <form action="/presenca/visitante" method="POST" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                <div>
                    <label for="nome_visitante" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Nome do Visitante</label>
                    <input id="nome_visitante" type="text" name="nome_visitante" list="visitantes_historico" required placeholder="Ex: João da Silva" oninput="atualizarQtdVisitaVisitante(this.value)" class="w-full px-3 py-2.5 bg-white border border-purple-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-purple-600">
                    <datalist id="visitantes_historico">
                        <?php foreach ($visitantesHistorico ?? [] as $vh): ?>
                            <option value="<?= SecurityHelper::e($vh['nome_visitante']) ?>" data-proxima-visita="<?= (int)($vh['max_visitas'] + 1) ?>">
                                Visitante anterior (já foi <?= (int)$vh['max_visitas'] ?>x)
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label for="qtd_visitas" class="block text-[10px] font-bold text-purple-700 uppercase mb-1">Vezes que já foi à célula</label>
                    <input id="qtd_visitas" type="number" name="qtd_visitas" value="1" min="1" required class="w-full px-3 py-2.5 bg-white border border-purple-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="toggleForm('formVisitante')" class="px-3 py-2 text-xs font-bold text-purple-600 hover:text-purple-800">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-xs">Cadastrar Visitante</button>
                </div>
            </form>
        </div>

        <!-- Lista de Confirmados (Membros + Visitantes) -->
        <?php if (!empty($presencas)): ?>
            <?php 
            $loggedUserId = (int)($_SESSION['user']['id'] ?? 0);
            $canDeleteAny = SecurityHelper::hasPermissao('escala.delete');
            ?>
            <div class="space-y-2 pt-1">
                <?php foreach ($presencas as $p): ?>
                    <?php 
                    $isVisitante = ($p['tipo'] ?? '') === 'visitante' || empty($p['usuario_id']); 
                    $podeRemover = $canDeleteAny || 
                                   (!empty($p['usuario_id']) && (int)$p['usuario_id'] === $loggedUserId) || 
                                   (!empty($p['registrado_por_id']) && (int)$p['registrado_por_id'] === $loggedUserId);
                    ?>
                    <div class="flex items-center gap-3 p-3 rounded-2xl <?= $isVisitante ? 'bg-purple-50/60 border border-purple-100' : 'bg-emerald-50/60 border border-emerald-100' ?>">
                        <div class="w-8 h-8 rounded-xl <?= $isVisitante ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' ?> font-extrabold text-sm flex items-center justify-center shrink-0">
                            <?= strtoupper(substr($p['usuario_nome'] ?? '?', 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 truncate"><?= SecurityHelper::e($p['usuario_nome'] ?? '') ?></p>
                            <?php if ($isVisitante): ?>
                                <p class="text-[10px] text-purple-600 font-extrabold tracking-wide uppercase">
                                    Visitante • <?= (int)($p['qtd_visitas'] ?? 1) ?>ª visita à célula
                                </p>
                            <?php else: ?>
                                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide"><?= SecurityHelper::e($p['usuario_perfil'] ?? 'MEMBRO') ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($podeRemover): ?>
                            <form action="/presenca/cancelar" method="POST" data-confirm="Remover esta confirmação de presença?">
                                <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                                <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                                <input type="hidden" name="presenca_id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Remover presença">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
</script>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
