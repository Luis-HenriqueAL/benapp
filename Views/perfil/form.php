<?php
/**
 * View: Formulário de Criação / Edição de Perfil
 * 
 * Formulário unificado para criar ou editar um perfil customizado da célula,
 * com checkboxes de permissões organizadas por módulo.
 *
 * @var array|null $perfilEdicao Dados do perfil em edição, ou null para criação.
 * @var array $permissoesDisponiveis Mapa chave => rótulo das permissões disponíveis.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

$isEdit = !empty($perfilEdicao);
$action = $isEdit ? '/perfil/update' : '/perfil/store';
$titulo = $isEdit ? 'Editar Perfil' : 'Novo Perfil';
$permissoesAtivas = $isEdit ? ($perfilEdicao['permissoes'] ?? []) : [];

// Agrupamento visual das permissões por módulo
$grupos = [
    'Escalas'          => ['escala.view', 'escala.create', 'escala.delete'],
    'Membros & Equipe'  => ['usuarios.view', 'usuarios.manage'],
    'Célula & Config.'  => ['celula.edit', 'liturgia.momentos', 'perfil.manage'],
];

ob_start();
?>
<div class="space-y-5 max-w-md mx-auto pb-6">

    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center space-x-3.5">
        <a href="/perfil" class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0" aria-label="Voltar para perfis">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight"><?= $titulo ?></h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Defina o nome e as permissões do perfil</p>
        </div>
    </div>

    <form action="<?= $action ?>" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$perfilEdicao['id'] ?>">
        <?php endif; ?>

        <!-- Dados básicos -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-4">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80">1</div>
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Identificação</h3>
            </div>

            <div>
                <label for="nome" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nome do Perfil <span class="text-rose-500">*</span></label>
                <input id="nome" type="text" name="nome" required placeholder="Ex: Monitor, Auxiliar, Diácono..."
                    value="<?= SecurityHelper::e($perfilEdicao['nome'] ?? '') ?>"
                    class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
            </div>
            <div>
                <label for="descricao" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Descrição <span class="text-slate-300 font-medium">(opcional)</span></label>
                <textarea id="descricao" name="descricao" rows="2" placeholder="Descreva a função ou responsabilidade deste perfil..."
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all resize-none"><?= SecurityHelper::e($perfilEdicao['descricao'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Permissões por módulo -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-5">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80">2</div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Permissões de Acesso</h3>
                    <p class="text-[10px] text-slate-400 font-medium">Selecione o que este perfil pode fazer</p>
                </div>
            </div>

            <?php foreach ($grupos as $grupoNome => $chaves): ?>
                <div class="space-y-2.5">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block"><?= SecurityHelper::e($grupoNome) ?></span>
                    <?php foreach ($chaves as $chave): ?>
                        <?php
                        $rotulo = $permissoesDisponiveis[$chave] ?? $chave;
                        $checked = in_array($chave, $permissoesAtivas, true) ? 'checked' : '';
                        ?>
                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl border border-slate-200 bg-slate-50/70 cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition-all has-[:checked]:border-blue-400 has-[:checked]:bg-blue-50/50">
                            <input type="checkbox" name="permissoes[]" value="<?= SecurityHelper::e($chave) ?>" <?= $checked ?>
                                class="w-4.5 h-4.5 rounded-lg text-blue-600 border-slate-300 focus:ring-blue-500 accent-blue-600">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-bold text-slate-800 block"><?= SecurityHelper::e($rotulo) ?></span>
                                <span class="text-[10px] text-slate-400 font-medium"><?= SecurityHelper::e($chave) ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-98 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition-all text-sm tracking-wide">
            <?= $isEdit ? 'Salvar Alterações' : 'Criar Perfil' ?>
        </button>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
