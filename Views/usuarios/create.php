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
    <!-- Cabeçalho com Ação Voltar -->
    <div class="flex items-center space-x-3 mb-1">
        <a href="/usuarios" class="p-2.5 rounded-2xl bg-white text-slate-600 shadow-md shadow-slate-200/50 border border-slate-100 hover:bg-slate-50 transition-all active:scale-90 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Novo Usuário</h2>
            <p class="text-xs text-slate-500 font-medium">Cadastre um voluntário ou líder</p>
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
                    <option value="MEMBRO">Voluntário / Membro</option>
                    <option value="LIDER">Líder de Célula</option>
                </select>
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
