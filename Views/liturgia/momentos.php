<?php 
/**
 * View: Gerenciamento de Momentos da Liturgia (Parametrização)
 * 
 * Interface moderna mobile-first para o cadastro simples e gerenciamento dos momentos
 * pré-definidos da liturgia (Louvor, Palavra, Oração, Janta, etc.).
 * 
 * @var array $momentos Lista de momentos predefinidos da célula.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

ob_start(); 
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>
<div class="space-y-5 max-w-md mx-auto pb-6">
    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center space-x-3.5">
        <a href="/" class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0" aria-label="Voltar para início">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Momentos da Liturgia</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Cadastre os momentos padrão para os encontros</p>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= SecurityHelper::e($flashSuccess) ?>
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 font-bold">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Form de Adição Rápida de Momento -->
    <?php if (SecurityHelper::hasPermissao('liturgia.momentos')): ?>
    <div class="bg-white p-5 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100">
        <form action="/liturgia/momentos/store" method="POST" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
            <label for="titulo" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Novo Momento</label>
            <div class="flex gap-2">
                <input id="titulo" type="text" name="titulo" required placeholder="Ex: Louvor, Janta, Oração..." class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 text-white font-extrabold px-5 rounded-2xl shadow-md shadow-blue-500/20 text-xs transition-all tracking-wide flex items-center gap-1">
                    Adicionar
                </button>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="is_louvor" name="is_louvor" value="1" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                <label for="is_louvor" class="text-xs font-bold text-slate-700 flex items-center gap-1 cursor-pointer">
                    <span>🎵 Este momento envolve Louvor / Músicas (com cifras)</span>
                </label>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Lista de Momentos em Cards -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Momentos Cadastrados</h3>

        <?php if (empty($momentos)): ?>
            <div class="bg-white rounded-3xl p-8 text-center text-slate-400 text-xs font-semibold border border-slate-100 shadow-sm">
                Nenhum momento cadastrado.
            </div>
        <?php else: ?>
            <?php foreach ($momentos as $m): ?>
                <div class="bg-white rounded-3xl shadow-lg shadow-slate-200/40 border border-slate-100 p-4 flex justify-between items-center active:scale-[0.99] transition-all">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-2xl <?= !empty($m['is_louvor']) ? 'bg-purple-100 text-purple-700 border-purple-200' : 'bg-blue-50 text-blue-600 border-blue-100' ?> flex items-center justify-center font-extrabold text-xs border">
                            <?= !empty($m['is_louvor']) ? '🎵' : '#' . SecurityHelper::e($m['ordem']) ?>
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-900 leading-snug"><?= SecurityHelper::e($m['titulo']) ?></h4>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <?php if (!empty($m['is_louvor'])): ?>
                                    <span class="inline-block text-[9px] font-extrabold uppercase tracking-wider text-purple-700 bg-purple-100 border border-purple-200 px-1.5 py-0.5 rounded-md">🎵 Louvor</span>
                                <?php endif; ?>
                                <?php if (!empty($m['obrigatorio'])): ?>
                                    <span class="inline-block text-[9px] font-bold uppercase tracking-wider text-amber-700 bg-amber-100 border border-amber-200 px-1.5 py-0.5 rounded-md">Obrigatório</span>
                                <?php else: ?>
                                    <span class="inline-block text-[9px] font-semibold text-slate-400">Personalizado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($m['obrigatorio']) && SecurityHelper::hasPermissao('liturgia.momentos')): ?>
                        <form action="/liturgia/momentos/delete" method="POST" data-confirm="Deseja realmente remover este momento?">
                            <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="p-2 rounded-xl text-rose-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 transition-colors" title="Remover momento" aria-label="Remover momento">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
