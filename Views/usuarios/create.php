<?php 
/**
 * View: Cadastro de Usuários
 * 
 * Interface moderna mobile-first para o cadastro de membros e líderes,
 * contendo validação visual, proteção CSRF e componentes estilizados com Tailwind CSS.
 */
ob_start(); 
?>
<div class="space-y-5 max-w-md mx-auto">
    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center space-x-3.5">
        <a href="/usuarios" class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0" aria-label="Voltar para usuários">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Novo Usuário</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Cadastre um voluntário ou líder</p>
        </div>
    </div>

    <!-- Card de Formulário -->
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 sm:p-7">
        <form action="/usuarios/store" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= \Helpers\SecurityHelper::generateCsrfToken() ?>">

            <div>
                <label for="nome" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nome Completo</label>
                <input id="nome" type="text" name="nome" required placeholder="Ex: João da Silva" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
            </div>

            <div>
                <label for="email" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">E-mail</label>
                <input id="email" type="email" name="email" required placeholder="joao@email.com" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
            </div>

            <div>
                <label for="senha" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Senha Inicial</label>
                <input id="senha" type="password" name="senha" required placeholder="••••••••" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
            </div>

            <div>
                <label for="perfil" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Perfil de Acesso</label>
                <select id="perfil" name="perfil" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all appearance-none">
                    <optgroup label="Perfis do Sistema">
                        <option value="MEMBRO">Voluntário / Membro</option>
                        <option value="LIDER">Líder de Célula</option>
                    </optgroup>
                    <?php if (!empty($perfilsCustomizados)): ?>
                        <optgroup label="Perfis Customizados">
                            <?php foreach ($perfilsCustomizados as $pc): ?>
                                <option value="<?= htmlspecialchars($pc['nome'], ENT_QUOTES) ?>">
                                    <?= htmlspecialchars($pc['nome'], ENT_QUOTES) ?>
                                    <?php if (!empty($pc['descricao'])): ?>
                                        — <?= htmlspecialchars($pc['descricao'], ENT_QUOTES) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>

            <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-2xl flex items-start gap-3 mt-1">
                <input type="checkbox" id="is_lider_principal" name="is_lider_principal" value="1" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 mt-0.5 shrink-0">
                <label for="is_lider_principal" class="text-xs font-bold text-slate-800 cursor-pointer select-none">
                    ⭐ Líder Principal da Célula
                    <span class="block text-[10px] font-normal text-slate-500 leading-tight mt-0.5">Receberá automaticamente as atribuições de Louvor e Palavra na geração de escala.</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-98 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition-all text-sm tracking-wide mt-4">
                Cadastrar Usuário
            </button>
        </form>
    </div>
</div>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
