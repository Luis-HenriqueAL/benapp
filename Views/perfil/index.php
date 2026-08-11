<?php
/**
 * View: Lista de Perfis e Permissões
 * 
 * Exibe todos os perfis customizados da célula com suas permissões atribuídas.
 * Permite criar, editar e remover perfis. Acesso restrito a LIDER.
 *
 * @var array $perfis Lista de perfis com permissões.
 * @var array $permissoesDisponiveis Mapa chave => rótulo das permissões do sistema.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

ob_start();
?>
<div class="space-y-5 max-w-md mx-auto pb-6">

    <!-- Header -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Perfis & Permissões</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Controle de acesso por módulo</p>
        </div>
        <?php if (SecurityHelper::hasPermissao('perfil.manage')): ?>
        <a href="/perfil/create" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 text-white font-extrabold px-4 py-2.5 rounded-2xl shadow-md shadow-blue-500/20 text-xs transition-all flex items-center gap-1.5 tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Novo Perfil
        </a>
        <?php endif; ?>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            <?= SecurityHelper::e($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <!-- Perfil LIDER (nativo, não editável) -->
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl p-5 border border-amber-200/60 shadow-md shadow-amber-100/40">
        <div class="flex justify-between items-start mb-3">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 bg-amber-100 border border-amber-300 text-amber-800 text-[10px] font-extrabold uppercase tracking-wider rounded-full mb-1.5">Nativo do Sistema</span>
                <h3 class="font-extrabold text-slate-900 text-base">Líder (LIDER)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Acesso irrestrito a todos os módulos</p>
            </div>
            <div class="w-9 h-9 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <?php foreach ($permissoesDisponiveis as $chave => $rotulo): ?>
                <span class="px-2.5 py-1 bg-amber-100/80 text-amber-800 text-[10px] font-bold rounded-xl border border-amber-200/70">
                    <?= SecurityHelper::e($rotulo) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Perfil MEMBRO (nativo, não editável) -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-md shadow-slate-100/50">
        <div class="flex justify-between items-start mb-3">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 bg-slate-100 border border-slate-200 text-slate-600 text-[10px] font-extrabold uppercase tracking-wider rounded-full mb-1.5">Nativo do Sistema</span>
                <h3 class="font-extrabold text-slate-900 text-base">Membro (MEMBRO)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Acesso básico: ver escalas</p>
            </div>
            <div class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-xl border border-slate-200">Ver Escalas</span>
        </div>
    </div>

    <!-- Separador -->
    <?php if (!empty($perfis)): ?>
        <div class="flex items-center gap-3 px-1">
            <div class="flex-1 h-px bg-slate-200"></div>
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Perfis Customizados</span>
            <div class="flex-1 h-px bg-slate-200"></div>
        </div>
    <?php endif; ?>

    <!-- Perfis Customizados -->
    <?php if (empty($perfis)): ?>
        <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-xl shadow-slate-200/50 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3 class="text-sm font-extrabold text-slate-800">Nenhum perfil customizado</h3>
            <p class="text-xs text-slate-400 font-medium">Crie perfis com permissões específicas para sua equipe.</p>
        </div>
    <?php else: ?>
        <?php foreach ($perfis as $p): ?>
            <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xl shadow-slate-200/40 space-y-3.5">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base leading-snug"><?= SecurityHelper::e($p['nome']) ?></h3>
                        <?php if (!empty($p['descricao'])): ?>
                            <p class="text-xs text-slate-400 font-medium mt-0.5"><?= SecurityHelper::e($p['descricao']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (SecurityHelper::hasPermissao('perfil.manage')): ?>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="/perfil/edit?id=<?= (int)$p['id'] ?>" class="p-2 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 transition active:scale-90" aria-label="Editar perfil">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="/perfil/delete" method="POST" data-confirm="Remover o perfil '<?= SecurityHelper::e($p['nome']) ?>'? Usuários com este perfil voltarão a ter acesso básico.">
                            <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="p-2 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-100 transition active:scale-90" aria-label="Excluir perfil">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Permissões atribuídas -->
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block mb-2">Permissões</span>
                    <?php if (empty($p['permissoes'])): ?>
                        <span class="text-xs text-slate-400 italic">Nenhuma permissão atribuída</span>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($p['permissoes'] as $chave): ?>
                                <?php $rotulo = $permissoesDisponiveis[$chave] ?? $chave; ?>
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-xl border border-blue-100">
                                    <?= SecurityHelper::e($rotulo) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
