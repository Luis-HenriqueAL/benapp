<?php 
/**
 * View: Criação / Montagem Dinâmica de Escala e Liturgia
 * 
 * Interface moderna de alta fidelidade (State-of-the-Art Web-Mobile UI).
 * Permite cadastrar evento e associar momentos litúrgicos com voluntários dinamicamente,
 * com autopreenchimento das informações da célula e reordenação de momentos (Subir/Descer / Drag & Drop).
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

// Extrai e sanitiza os dados da célula enviadas pelo controller
$nomeCelula = !empty($celulaInfo['nome']) ? $celulaInfo['nome'] : (!empty($celulaInfo['nome_celula']) ? $celulaInfo['nome_celula'] : 'Célula Boas Novas');
$horarioCelula = !empty($celulaInfo['horario']) ? substr($celulaInfo['horario'], 0, 5) : '19:30';
$diaSemanaCelula = !empty($celulaInfo['dia_semana']) ? $celulaInfo['dia_semana'] : 'Quarta-feira';

// Momentos predefinidos (template)
$templates = !empty($momentosPredefinidos) ? $momentosPredefinidos : [
    ['id' => 1, 'titulo' => 'Quebra-Gelo / Recepção', 'ordem' => 1, 'duracao_minutos' => 15, 'obrigatorio' => false],
    ['id' => 2, 'titulo' => 'Louvor e Adoração', 'ordem' => 2, 'duracao_minutos' => 20, 'obrigatorio' => false],
    ['id' => 3, 'titulo' => 'Estudo / Palavra', 'ordem' => 3, 'duracao_minutos' => 40, 'obrigatorio' => true],
    ['id' => 4, 'titulo' => 'Oração e Avisos', 'ordem' => 4, 'duracao_minutos' => 15, 'obrigatorio' => false]
];

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

    <!-- Card Informativo da Célula (Autopreenchimento Automático) -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-4 text-white shadow-lg shadow-blue-500/20 flex justify-between items-center">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200 block">Célula Vinculada</span>
            <h3 class="text-base font-extrabold tracking-tight"><?= SecurityHelper::e($nomeCelula) ?></h3>
            <p class="text-xs text-blue-100 font-medium">Encontro habitual: <?= SecurityHelper::e($diaSemanaCelula) ?> às <?= SecurityHelper::e($horarioCelula) ?>h</p>
        </div>
        <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white font-extrabold text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
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
                <input id="evento" type="text" name="evento" value="Culto de Célula - <?= SecurityHelper::e($nomeCelula) ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all" placeholder="Ex: Culto de Domingo">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="data" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Data</label>
                    <input id="data" type="date" name="data" required class="w-full px-3.5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                </div>
                <div>
                    <label for="hora" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Hora (Horário Célula)</label>
                    <input id="hora" type="time" name="hora" value="<?= SecurityHelper::e($horarioCelula) ?>" required class="w-full px-3.5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                </div>
            </div>
            
            <div>
                <label for="lider_id" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Líder Responsável</label>
                <select id="lider_id" name="lider_id" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all appearance-none">
                    <option value="" disabled>Selecione o líder responsável...</option>
                    <?php if (!empty($voluntarios) && is_array($voluntarios)): ?>
                        <?php foreach ($voluntarios as $vol): ?>
                            <option value="<?= (int)$vol['id'] ?>" <?= ($vol['id'] == 1 || ($vol['perfil'] ?? '') === 'LIDER') ? 'selected' : '' ?>>
                                <?= SecurityHelper::e($vol['nome']) ?> (<?= SecurityHelper::e($vol['perfil'] ?? 'Membro') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1" selected>João Silva (Líder Principal)</option>
                        <option value="2">Maria Alves (Coordenadora)</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <!-- Bloco de Liturgia e Momentos Dinâmicos com Reordenação -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                        2
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Liturgia & Momentos</h3>
                        <p class="text-[10px] text-slate-400 font-medium">Reordene os momentos usando ▲/▼ ou arrastando</p>
                    </div>
                </div>
                <button type="button" id="btnAddMomento" class="text-blue-600 hover:text-blue-700 font-extrabold text-xs flex items-center bg-blue-50 hover:bg-blue-100 border border-blue-100/80 px-3.5 py-2 rounded-2xl active:scale-95 transition-all shadow-xs">
                    <svg class="w-4 h-4 mr-1" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Add Momento
                </button>
            </div>
            
            <div id="momentosList" class="space-y-4">
                <?php foreach ($templates as $idx => $momento): ?>
                    <div class="momento-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative cursor-grab transition-all duration-200 hover:border-blue-300" draggable="true">
                        <!-- Controls Header (Reordenação & Exclusão) -->
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-200/50">
                            <div class="flex items-center space-x-2">
                                <span class="drag-handle text-slate-400 hover:text-slate-600 cursor-grab p-1" title="Arrastar para reordenar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                </span>
                                <span class="momento-badge font-extrabold text-xs text-blue-600 bg-blue-100/70 px-2 py-0.5 rounded-lg">#<?= $idx + 1 ?></span>
                                <?php if (!empty($momento['obrigatorio'])): ?>
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-amber-700 bg-amber-100 border border-amber-200 px-1.5 py-0.5 rounded-md">Obrigatório</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button type="button" class="btn-move-up p-1 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Subir momento" aria-label="Subir momento">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                                </button>
                                <button type="button" class="btn-move-down p-1 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Descer momento" aria-label="Descer momento">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <?php if (empty($momento['obrigatorio'])): ?>
                                    <button type="button" aria-label="Remover momento" class="remove-momento text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label data-field-for="titulo" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Título do Momento</label>
                                <input data-field="titulo" type="text" name="momentos[<?= $idx ?>][titulo]" value="<?= SecurityHelper::e($momento['titulo']) ?>" placeholder="Ex: Louvor e Adoração" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800 placeholder-slate-400" required>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label data-field-for="inicio" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Início</label>
                                    <input data-field="inicio" type="time" name="momentos[<?= $idx ?>][inicio]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                                </div>
                                <div>
                                    <label data-field-for="fim" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fim</label>
                                    <input data-field="fim" type="time" name="momentos[<?= $idx ?>][fim]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                                </div>
                            </div>
                            <div>
                                <label data-field-for="voluntario_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Voluntário Responsável</label>
                                <select data-field="voluntario_id" name="momentos[<?= $idx ?>][voluntario_id]" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                                    <option value="">Sem voluntário atribuído...</option>
                                    <?php if (!empty($voluntarios) && is_array($voluntarios)): ?>
                                        <?php foreach ($voluntarios as $vol): ?>
                                            <option value="<?= (int)$vol['id'] ?>">
                                                <?= SecurityHelper::e($vol['nome']) ?> (<?= SecurityHelper::e($vol['perfil'] ?? 'Voluntário') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="1">João Silva (Ministro)</option>
                                        <option value="3">Pedro Técnico (Som)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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

        /**
         * Reindexa a lista de momentos (atualiza badges, names e ids no DOM para manter coerência dos dados submetidos).
         */
        function reindexMomentos() {
            const items = momentosList.querySelectorAll('.momento-item');
            items.forEach((item, index) => {
                // Atualiza badge numérico (#1, #2, ...)
                const badge = item.querySelector('.momento-badge');
                if (badge) badge.textContent = `#${index + 1}`;

                // Atualiza name e id de todos os campos de formulário no card
                const fields = item.querySelectorAll('[data-field]');
                fields.forEach(input => {
                    const fieldName = input.getAttribute('data-field');
                    input.name = `momentos[${index}][${fieldName}]`;
                    input.id = `momento_${fieldName}_${index}`;
                });

                // Atualiza labels associadas
                const labels = item.querySelectorAll('[data-field-for]');
                labels.forEach(label => {
                    const fieldFor = label.getAttribute('data-field-for');
                    label.htmlFor = `momento_${fieldFor}_${index}`;
                });
            });
        }

        // Adicionar novo momento dinamicamente
        btnAddMomento.addEventListener('click', () => {
            const nextIndex = momentosList.children.length;
            const template = `
                <div class="momento-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative cursor-grab transition-all duration-300 opacity-0 translate-y-4 hover:border-blue-300" draggable="true">
                    <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-200/50">
                        <div class="flex items-center space-x-2">
                            <span class="drag-handle text-slate-400 hover:text-slate-600 cursor-grab p-1" title="Arrastar para reordenar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                            </span>
                            <span class="momento-badge font-extrabold text-xs text-blue-600 bg-blue-100/70 px-2 py-0.5 rounded-lg">#${nextIndex + 1}</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <button type="button" class="btn-move-up p-1 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Subir momento" aria-label="Subir momento">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                            <button type="button" class="btn-move-down p-1 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Descer momento" aria-label="Descer momento">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <button type="button" aria-label="Remover momento" class="remove-momento text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label data-field-for="titulo" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Título do Momento</label>
                            <input data-field="titulo" type="text" name="momentos[${nextIndex}][titulo]" placeholder="Ex: Oração de Intercessão" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800 placeholder-slate-400" required>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label data-field-for="inicio" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Início</label>
                                <input data-field="inicio" type="time" name="momentos[${nextIndex}][inicio]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                            </div>
                            <div>
                                <label data-field-for="fim" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fim</label>
                                <input data-field="fim" type="time" name="momentos[${nextIndex}][fim]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                            </div>
                        </div>
                        <div>
                            <label data-field-for="voluntario_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Voluntário Responsável</label>
                            <select data-field="voluntario_id" name="momentos[${nextIndex}][voluntario_id]" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                                <option value="">Sem voluntário atribuído...</option>
                                <?php if (!empty($voluntarios) && is_array($voluntarios)): ?>
                                    <?php foreach ($voluntarios as $vol): ?>
                                        <option value="<?= (int)$vol['id'] ?>"><?= SecurityHelper::e($vol['nome']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">João Silva (Ministro)</option>
                                    <option value="3">Pedro Técnico (Som)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            
            momentosList.insertAdjacentHTML('beforeend', template);
            const newItem = momentosList.lastElementChild;
            requestAnimationFrame(() => {
                newItem.classList.remove('opacity-0', 'translate-y-4');
            });
            reindexMomentos();
        });

        // Delegação de eventos para Subir, Descer e Remover
        momentosList.addEventListener('click', (e) => {
            const btnUp = e.target.closest('.btn-move-up');
            const btnDown = e.target.closest('.btn-move-down');
            const btnRemove = e.target.closest('.remove-momento');
            const item = e.target.closest('.momento-item');

            if (btnUp && item) {
                const prev = item.previousElementSibling;
                if (prev && prev.classList.contains('momento-item')) {
                    momentosList.insertBefore(item, prev);
                    reindexMomentos();
                }
            } else if (btnDown && item) {
                const next = item.nextElementSibling;
                if (next && next.classList.contains('momento-item')) {
                    momentosList.insertBefore(next, item);
                    reindexMomentos();
                }
            } else if (btnRemove && item) {
                item.style.transform = 'scale(0.95)';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    reindexMomentos();
                }, 200);
            }
        });

        // Drag & Drop Limpo em JavaScript Native HTML5
        let draggedItem = null;
        momentosList.addEventListener('dragstart', (e) => {
            const item = e.target.closest('.momento-item');
            if (item) {
                draggedItem = item;
                setTimeout(() => item.classList.add('opacity-40', 'bg-blue-50/50'), 0);
            }
        });

        momentosList.addEventListener('dragend', (e) => {
            if (draggedItem) {
                draggedItem.classList.remove('opacity-40', 'bg-blue-50/50');
                draggedItem = null;
                reindexMomentos();
            }
        });

        momentosList.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!draggedItem) return;
            const target = e.target.closest('.momento-item');
            if (target && target !== draggedItem) {
                const rect = target.getBoundingClientRect();
                const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
                momentosList.insertBefore(draggedItem, next ? target.nextSibling : target);
            }
        });
    });
</script>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>

