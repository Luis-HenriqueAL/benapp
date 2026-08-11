<div class="space-y-4 max-w-lg mx-auto">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Novo Usuário</h2>
            <p class="text-sm text-gray-500 font-medium">Cadastre um novo voluntário ou líder</p>
        </div>
        <a href="/usuarios" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Voltar</a>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-xl">
        <form action="/usuarios/create" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= \Helpers\SecurityHelper::generateCsrfToken() ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Nome Completo</label>
                <input type="text" name="nome" required placeholder="Ex: João da Silva" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none text-sm transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">E-mail</label>
                <input type="email" name="email" required placeholder="exemplo@celula.com" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none text-sm transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Senha</label>
                <input type="password" name="senha" required placeholder="••••••••" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none text-sm transition-all">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Perfil</label>
                    <select name="perfil" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none text-sm transition-all">
                        <option value="MEMBRO">Membro</option>
                        <option value="LIDER">Líder</option>
                        <option value="VOLUNTARIO">Voluntário</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none text-sm transition-all">
                        <option value="ativo" selected>Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="/usuarios" class="px-5 py-3 rounded-full text-sm font-semibold text-gray-500 hover:bg-gray-100 transition-all">Cancelar</a>
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-sm font-bold shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                    Salvar Usuário
                </button>
            </div>
        </form>
    </div>
</div>
