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
$tema = !empty($liturgia['tema']) ? $liturgia['tema'] : 'Culto de Célula';
$atribuicoes = $liturgia['atribuicoes'] ?? [];

$nomeCelula = !empty($celulaInfo['nome']) ? $celulaInfo['nome'] : (!empty($celulaInfo['nome_celula']) ? $celulaInfo['nome_celula'] : 'Célula Boas Novas');
$horarioCelula = !empty($celulaInfo['horario']) ? substr($celulaInfo['horario'], 0, 5) : '19:30';

ob_start(); 
?>
<div class="space-y-5 max-w-md mx-auto pb-6">
    <!-- Cabeçalho com Ação Voltar e Excluir (LIDER) -->
    <div class="flex items-center space-x-3 mb-1">
        <a href="/escala" class="p-2.5 rounded-2xl bg-white text-slate-600 shadow-md shadow-slate-200/50 border border-slate-100 hover:bg-slate-50 transition-all active:scale-90 flex items-center justify-center" aria-label="Voltar para escalas">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex-1">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Detalhes da Escala</h2>
            <p class="text-xs text-slate-500 font-medium">Programação e voluntários escalados</p>
        </div>
        <?php if (($_SESSION['user']['perfil'] ?? '') === 'LIDER'): ?>
            <form action="/escala/delete" method="POST" data-confirm="Tem certeza que deseja excluir este evento? Esta ação é irreversível.">
                <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                <button type="submit" class="p-2.5 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 active:scale-90 transition-all shadow-md shadow-rose-100/50 flex items-center justify-center" aria-label="Excluir evento">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </form>
        <?php endif; ?> 
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

    <!-- Seção de Presenças -->
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
                    <button type="submit" class="flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-extrabold text-xs transition-all hover:bg-emerald-100 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Confirmado
                    </button>
                </form>
            <?php else: ?>
                <form action="/presenca/confirmar" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                    <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
                    <button type="submit" class="flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-extrabold text-xs transition-all shadow-md shadow-blue-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Confirmar Presença
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (!empty($presencas)): ?>
            <div class="space-y-2">
                <?php foreach ($presencas as $p): ?>
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 font-extrabold text-sm flex items-center justify-center shrink-0">
                            <?= strtoupper(substr($p['usuario_nome'] ?? '?', 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 truncate"><?= SecurityHelper::e($p['usuario_nome'] ?? '') ?></p>
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide"><?= SecurityHelper::e($p['usuario_perfil'] ?? '') ?></p>
                        </div>
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-xs text-slate-400 text-center py-2 italic">Nenhuma confirmação de presença ainda.</p>
        <?php endif; ?>
    </div>
</div>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
