<div class="space-y-4 max-w-lg mx-auto">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Usuários</h2>
            <p class="text-sm text-gray-500 font-medium">Gestão da equipe e membros da célula</p>
        </div>
        <a href="/usuarios/create" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-full shadow-md transition-all active:scale-95">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Novo
        </a>
    </div>

    <?php if (empty($usuarios)): ?>
        <div class="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <p class="text-gray-500 font-medium text-sm">Nenhum usuário cadastrado.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($usuarios as $usr): ?>
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center justify-between transition-all hover:shadow-md">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                            <?= \Helpers\SecurityHelper::e(strtoupper(substr($usr['nome'], 0, 1))) ?>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="font-bold text-gray-900 text-sm"><?= \Helpers\SecurityHelper::e($usr['nome']) ?></h3>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider <?= ($usr['perfil'] ?? '') === 'LIDER' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' ?>">
                                    <?= \Helpers\SecurityHelper::e($usr['perfil'] ?? 'MEMBRO') ?>
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider <?= ($usr['status'] ?? 'ativo') === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                    <?= \Helpers\SecurityHelper::e($usr['status'] ?? 'ativo') ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5"><?= \Helpers\SecurityHelper::e($usr['email']) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="/usuarios/edit?id=<?= $usr['id'] ?>" class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="/usuarios/delete?id=<?= $usr['id'] ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                            <input type="hidden" name="csrf_token" value="<?= \Helpers\SecurityHelper::generateCsrfToken() ?>">
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 transition-colors" title="Excluir">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
