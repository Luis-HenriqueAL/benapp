<?php 
/**
 * View: Dashboard / Lista de Escalas Reais
 * 
 * Exibe a relação de escalas e cultos reais cadastrados no banco de dados para a célula logada,
 * eliminando dados mockados e permitindo navegar para a visualização detalhada de cada evento.
 * 
 * @var array $escalas Lista de escalas reais criadas na célula.
 * @var array|null $celulaInfo Informações cadastrais da célula.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

ob_start(); 
?>
<div class="space-y-5 max-w-md mx-auto">
    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Escalas da Célula</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Encontros e voluntários alocados</p>
        </div>
        <a href="/escala/create" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 text-white font-extrabold px-4 py-2.5 rounded-2xl shadow-md shadow-blue-500/20 text-xs transition-all flex items-center gap-1.5 tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Nova
        </a>
    </div>

    <!-- Lista de Escalas em Cards (100% Reais do Banco) -->
    <div class="space-y-3.5">
        <?php if (empty($escalas)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-xl shadow-slate-200/50 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 mx-auto flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800">Nenhuma escala cadastrada</h3>
                <p class="text-xs text-slate-400 font-medium">Clique no botão "Nova" acima para cadastrar a primeira escala da sua célula.</p>
                <a href="/escala/create" class="inline-block bg-blue-600 text-white text-xs font-extrabold px-4 py-2.5 rounded-2xl shadow-md shadow-blue-500/20">
                    Cadastrar Primeira Escala
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($escalas as $e): ?>
                <?php 
                $dataFormatted = !empty($e['data_culto']) ? date('d/m/Y', strtotime($e['data_culto'])) : 'Data a definir';
                $tema = !empty($e['tema']) ? $e['tema'] : 'Culto de Célula';
                $totalVol = (int)($e['total_voluntarios'] ?? 0);
                ?>
                <a href="/escala/show?id=<?= $e['liturgia_id'] ?>" class="block bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 p-5 active:scale-[0.98] transition-all group">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200/60 text-[10px] font-extrabold uppercase tracking-wider rounded-full mb-2">
                                <?= SecurityHelper::e($tema) ?>
                            </span>
                            <h3 class="text-lg font-extrabold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors">
                                <?= SecurityHelper::e($dataFormatted) ?>
                            </h3>
                        </div>
                        <div class="text-right flex flex-col items-end">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-0.5">Voluntários</span>
                            <span class="text-base font-black text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-xl border border-blue-100">
                                <?= $totalVol ?>
                            </span>
                        </div>
                    </div>

                    <div class="pt-3.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Ver detalhes e liturgia
                        </span>
                        <div class="w-8 h-8 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
