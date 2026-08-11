<?php 
require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;
ob_start(); 
?>
<div class="max-w-lg mx-auto pb-6">
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Nova Escala</h2>
        <p class="text-sm text-gray-500 font-medium mt-1">Configure o evento e a liturgia.</p>
    </div>

    <form id="escalaForm" action="/escala/store" method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
        
        <!-- Detalhes Gerais -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 space-y-5">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <span class="font-bold text-sm">1</span>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">Detalhes do Culto</h3>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nome do Evento</label>
                <input type="text" name="evento" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors" placeholder="Ex: Culto de Domingo">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Data</label>
                    <input type="date" name="data" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Hora</label>
                    <input type="time" name="hora" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors text-gray-700">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Líder Responsável</label>
                <select name="lider_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors text-gray-700 appearance-none">
                    <option value="" disabled selected>Selecione um líder...</option>
                    <option value="1">João Silva</option>
                    <option value="2">Maria Alves</option>
                </select>
            </div>
        </div>

        <!-- Liturgia / Momentos -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <span class="font-bold text-sm">2</span>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg">Liturgia</h3>
                </div>
                <button type="button" id="btnAddMomento" class="text-blue-600 font-bold text-sm flex items-center bg-blue-50 px-3 py-1.5 rounded-lg active:bg-blue-100 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Add Momento
                </button>
            </div>
            
            <div id="momentosList" class="space-y-4">
                <!-- Momento Inicial -->
                <div class="momento-item p-4 border border-gray-200 rounded-xl bg-gray-50 relative">
                    <div class="space-y-4">
                        <div>
                            <input type="text" name="momentos[0][titulo]" placeholder="Título do momento (ex: Louvor)" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none font-semibold text-gray-800 placeholder-gray-400" required>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-1/2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Início</label>
                                <input type="time" name="momentos[0][inicio]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Fim</label>
                                <input type="time" name="momentos[0][fim]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                            </div>
                        </div>
                        <div>
                            <select name="momentos[0][voluntario_id]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm text-gray-700">
                                <option value="">Sem responsável específico...</option>
                                <option value="1">João Silva (Ministro)</option>
                                <option value="3">Pedro Técnico (Som)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 px-4 rounded-xl shadow-[0_4px_14px_0_rgba(37,99,235,0.39)] hover:shadow-[0_6px_20px_rgba(37,99,235,0.23)] hover:bg-blue-700 active:scale-[0.98] transition-all flex justify-center items-center text-lg">
            Salvar Escala
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
                <div class="momento-item p-4 border border-gray-200 rounded-xl bg-gray-50 relative mt-4 transform transition-all duration-300 opacity-0 translate-y-4">
                    <button type="button" class="remove-momento absolute -top-3 -right-3 bg-red-100 text-red-600 rounded-full p-1.5 shadow-sm hover:bg-red-200 active:scale-90 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="space-y-4">
                        <div>
                            <input type="text" name="momentos[${momentoIndex}][titulo]" placeholder="Título do momento (ex: Oração)" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none font-semibold text-gray-800 placeholder-gray-400" required>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-1/2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Início</label>
                                <input type="time" name="momentos[${momentoIndex}][inicio]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Fim</label>
                                <input type="time" name="momentos[${momentoIndex}][fim]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                            </div>
                        </div>
                        <div>
                            <select name="momentos[${momentoIndex}][voluntario_id]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm text-gray-700">
                                <option value="">Sem responsável específico...</option>
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
