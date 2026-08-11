<?php 
/**
 * View: Criação / Montagem Dinâmica de Escala e Liturgia
 * 
 * Interface moderna de alta fidelidade (State-of-the-Art Web-Mobile UI).
 * Permite cadastrar evento e associar momentos litúrgicos com voluntários dinamicamente.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;
ob_start(); 
?>
<div class="space-y-5 max-w-md mx-auto pb-6">
    <!-- Cabeçalho com Ação Voltar -->
    <div class="flex items-center space-x-3 mb-1">
        <a href="/" class="p-2.5 rounded-2xl bg-white text-slate-600 shadow-md shadow-slate-200/50 border border-slate-100 hover:bg-slate-50 transition-all active:scale-90 flex items-center justify-center" aria-label="Voltar para escalas">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Nova Escala</h2>
            <p class="text-xs text-slate-500 font-medium">Configure o culto e a liturgia da célula</p>
        </div>
    </div>

    <form id="escalaForm" action="/escala/store" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
        
        <!-- Detalhes Gerais do Culto -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-4">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-3 mb-1">
                <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                    1
                </div>
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Informações do Culto</h3>
            </div>
            
            <div>
                <label for="evento" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nome do Evento / Culto</label>
                <input id="evento" type="text" name="evento" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all" placeholder="Ex: Culto de Domingo">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="data" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Data</label>
                    <input id="data" type="date" name="data" required class="w-full px-3.5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                </div>
                <div>
                    <label for="hora" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Hora</label>
                    <input id="hora" type="time" name="hora" required class="w-full px-3.5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                </div>
            </div>
            
            <div>
                <label for="lider_id" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Líder Responsável</label>
                <select id="lider_id" name="lider_id" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all appearance-none">
                    <option value="" disabled selected>Selecione o líder responsável...</option>
                    <option value="1">João Silva (Líder Principal)</option>
                    <option value="2">Maria Alves (Coordenadora)</option>
                </select>
            </div>
        </div>

        <!-- Bloco de Liturgia e Momentos Dinâmicos -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                        2
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Liturgia do Culto</h3>
                </div>
                <button type="button" id="btnAddMomento" class="text-blue-600 hover:text-blue-700 font-extrabold text-xs flex items-center bg-blue-50 hover:bg-blue-100 border border-blue-100/80 px-3.5 py-2 rounded-2xl active:scale-95 transition-all shadow-xs">
                    <svg class="w-4 h-4 mr-1" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Add Momento
                </button>
            </div>
            
            <div id="momentosList" class="space-y-4">
                <!-- Momento Inicial -->
                <div class="momento-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative">
                    <div class="space-y-3.5">
                        <div>
                            <label for="momento_titulo_0" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Título do Momento</label>
                            <input id="momento_titulo_0" type="text" name="momentos[0][titulo]" placeholder="Ex: Louvor e Adoração" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800 placeholder-slate-400" required>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label for="momento_inicio_0" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Início</label>
                                <input id="momento_inicio_0" type="time" name="momentos[0][inicio]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                            </div>
                            <div>
                                <label for="momento_fim_0" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fim</label>
                                <input id="momento_fim_0" type="time" name="momentos[0][fim]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                            </div>
                        </div>
                        <div>
                            <label for="momento_voluntario_0" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Voluntário Responsável</label>
                            <select id="momento_voluntario_0" name="momentos[0][voluntario_id]" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                                <option value="">Sem voluntário atribuído...</option>
                                <option value="1">João Silva (Ministro)</option>
                                <option value="3">Pedro Técnico (Som)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-98 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition-all text-sm tracking-wide mt-2">
            Salvar Escala Completa
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnAddMomento = document.getElementById('btnAddMomento');
        const momentosList = document.getElementById('momentosList');
        let momentoIndex = 1;

        btnAddMomento.addEventListener('click', () => {
            const template = `
                <div class="momento-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative mt-4 transform transition-all duration-300 opacity-0 translate-y-4">
                    <button type="button" aria-label="Remover momento" class="remove-momento absolute -top-2.5 -right-2.5 bg-rose-100 hover:bg-rose-200 text-rose-600 rounded-full p-1.5 shadow-sm active:scale-90 transition border border-rose-200">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="space-y-3.5">
                        <div>
                            <label for="momento_titulo_${momentoIndex}" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Título do Momento</label>
                            <input id="momento_titulo_${momentoIndex}" type="text" name="momentos[${momentoIndex}][titulo]" placeholder="Ex: Oração de Abertura" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800 placeholder-slate-400" required>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label for="momento_inicio_${momentoIndex}" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Início</label>
                                <input id="momento_inicio_${momentoIndex}" type="time" name="momentos[${momentoIndex}][inicio]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                            </div>
                            <div>
                                <label for="momento_fim_${momentoIndex}" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fim</label>
                                <input id="momento_fim_${momentoIndex}" type="time" name="momentos[${momentoIndex}][fim]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                            </div>
                        </div>
                        <div>
                            <label for="momento_voluntario_${momentoIndex}" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Voluntário Responsável</label>
                            <select id="momento_voluntario_${momentoIndex}" name="momentos[${momentoIndex}][voluntario_id]" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                                <option value="">Sem voluntário atribuído...</option>
                                <option value="1">João Silva (Ministro)</option>
                                <option value="3">Pedro Técnico (Som)</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            
            momentosList.insertAdjacentHTML('beforeend', template);
            
            // Animation for new item
            const newItem = momentosList.lastElementChild;
            requestAnimationFrame(() => {
                newItem.classList.remove('opacity-0', 'translate-y-4');
            });
            
            momentoIndex++;
        });

        // Event delegation for remove buttons
        momentosList.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-momento');
            if (btn) {
                const item = btn.closest('.momento-item');
                item.style.transform = 'scale(0.95)';
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 250);
            }
        });
    });
</script>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
