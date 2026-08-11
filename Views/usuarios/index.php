<?php 
/**
 * View: Listagem de Usuários / Equipe
 * 
 * Interface mobile-first moderna (State-of-the-Art Web-Mobile UI).
 * Exibe a lista de voluntários/membros da célula em cards flutuantes com badges de perfil e status.
 * 
 * @var array $usuarios Lista de usuários cadastrados no sistema.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

ob_start(); 
?>
<div class="space-y-5 max-w-md mx-auto">
    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Equipe & Membros</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Voluntários e líderes da sua célula</p>
        </div>
        <?php if (SecurityHelper::hasPermissao('usuarios.manage')): ?>
        <a href="/usuarios/create" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 text-white font-extrabold px-4 py-2.5 rounded-2xl shadow-md shadow-blue-500/20 text-xs transition-all flex items-center gap-1.5 tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Novo
        </a>
        <?php endif; ?>
    </div>

    <!-- Lista de Usuários em Cards -->
    <div class="space-y-4">
        <?php if (empty($usuarios)): ?>
            <div class="bg-white rounded-3xl p-8 text-center text-slate-400 text-xs font-semibold border border-slate-100 shadow-sm">
                Nenhum usuário cadastrado até o momento.
            </div>
        <?php else: ?>
            <?php 
            $currentUserId = $_SESSION['user']['id'] ?? null;
            $canManage = SecurityHelper::hasPermissao('usuarios.manage');
            foreach ($usuarios as $u): 
                $isSelf = ($currentUserId && (int)($u['id'] ?? 0) === (int)$currentUserId);
            ?>
                <div class="bg-white rounded-3xl shadow-lg shadow-slate-200/40 border border-slate-100 p-5 flex justify-between items-center active:scale-[0.99] transition-all">
                    <div class="flex items-center space-x-4 min-w-0 flex-1 mr-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 text-blue-700 flex items-center justify-center font-extrabold text-base border border-blue-200/60 shadow-xs shrink-0">
                            <?= htmlspecialchars(substr($u['nome'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="min-w-0 space-y-1 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-extrabold text-slate-900 leading-tight truncate"><?= htmlspecialchars($u['nome'] ?? '') ?></h3>
                                <?php if ($isSelf): ?>
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase bg-blue-100 text-blue-700 rounded-md shrink-0">Você</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-slate-400 font-medium truncate"><?= htmlspecialchars($u['email'] ?? '') ?></p>
                            <div class="flex items-center gap-1.5 pt-1 flex-wrap">
                                <span class="px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded-full <?= ($u['perfil'] ?? 'MEMBRO') === 'LIDER' ? 'bg-purple-100 text-purple-700 border border-purple-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200/60' ?>">
                                    <?= htmlspecialchars($u['perfil'] ?? 'MEMBRO') ?>
                                </span>
                                <span class="px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded-full <?= ($u['status'] ?? 'ativo') === 'ativo' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200/60' : 'bg-rose-100 text-rose-700 border border-rose-200/60' ?>">
                                    <?= htmlspecialchars($u['status'] ?? 'ativo') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if ($canManage || $isSelf): ?>
                    <div class="flex items-center shrink-0">
                        <a href="/usuarios/edit?id=<?= $u['id'] ?? '' ?>" title="Editar Usuário" class="p-3 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors border border-slate-100 active:scale-90 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </a>
                    </div>
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
