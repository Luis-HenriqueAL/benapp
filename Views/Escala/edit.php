<?php 
/**
 * View: Edição de Escala e Liturgia
 * 
 * Interface moderna no estado da arte (Web-Mobile UI).
 * Permite alterar a data do evento, tema, reordenar momentos litúrgicos e atualizar a atribuição de voluntários.
 * 
 * @var array $liturgia Dados do culto e atribuições atuais.
 * @var array|null $celulaInfo Informações cadastrais da célula.
 * @var array $momentosPredefinidos Templates de momentos da célula.
 * @var array $voluntarios Lista de usuários membros da célula.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

$nomeCelula = !empty($celulaInfo['nome']) ? $celulaInfo['nome'] : (!empty($celulaInfo['nome_celula']) ? $celulaInfo['nome_celula'] : 'Célula Boas Novas');
$horarioCelula = !empty($celulaInfo['horario']) ? substr($celulaInfo['horario'], 0, 5) : '19:30';
$diaSemanaCelula = !empty($celulaInfo['dia_semana']) ? $celulaInfo['dia_semana'] : 'Quarta-feira';

$templates = !empty($momentosPredefinidos) ? $momentosPredefinidos : [
    ['id' => 1, 'titulo' => 'Quebra-Gelo / Recepção', 'ordem' => 1, 'obrigatorio' => false],
    ['id' => 2, 'titulo' => 'Louvor e Adoração', 'ordem' => 2, 'obrigatorio' => false],
    ['id' => 3, 'titulo' => 'Estudo / Palavra', 'ordem' => 3, 'obrigatorio' => true],
    ['id' => 4, 'titulo' => 'Oração e Avisos', 'ordem' => 4, 'obrigatorio' => false]
];

$atribuicoesExistentes = $liturgia['atribuicoes'] ?? [];
$dataCultoVal = !empty($liturgia['data_culto']) ? date('Y-m-d', strtotime($liturgia['data_culto'])) : date('Y-m-d');
$temaVal = !empty($liturgia['tema']) ? $liturgia['tema'] : "Encontro de Célula - {$nomeCelula}";

ob_start(); 
?>
<div class="space-y-5 max-w-md mx-auto pb-6">
    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center space-x-3.5">
        <a href="/escala/show?id=<?= (int)$liturgia['id'] ?>" class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0" aria-label="Voltar para detalhes">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Editar Liturgia</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Altere a data, momentos e voluntários</p>
        </div>
    </div>

    <!-- Card Informativo da Célula -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-4 text-white shadow-lg shadow-blue-500/20 flex justify-between items-center">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200 block">Célula Vinculada</span>
            <h3 class="text-base font-extrabold tracking-tight"><?= SecurityHelper::e($nomeCelula) ?></h3>
            <p class="text-xs text-blue-100 font-medium">Horário habitual: <?= SecurityHelper::e($diaSemanaCelula) ?> às <?= SecurityHelper::e($horarioCelula) ?>h</p>
        </div>
        <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white font-extrabold text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
    </div>

    <form id="escalaForm" action="/escala/update" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">
        <input type="hidden" name="liturgia_id" value="<?= (int)$liturgia['id'] ?>">
        <input type="hidden" name="evento" value="<?= SecurityHelper::e($temaVal) ?>">
        <input type="hidden" name="hora" value="<?= SecurityHelper::e($horarioCelula) ?>">
        
        <!-- Data do Culto -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-3">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-3 mb-1">
                <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                    1
                </div>
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Data da Reunião</h3>
            </div>
            
            <div>
                <label for="data_culto" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Data do Encontro</label>
                <input id="data_culto" type="date" name="data" value="<?= SecurityHelper::e($dataCultoVal) ?>" min="<?= date('Y-m-d') ?>" required class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                <div id="dateErrorMsg" class="hidden mt-2.5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-bold text-rose-700 flex items-center gap-2"></div>
            </div>
        </div>

        <!-- Bloco de Liturgia e Momentos Dinâmicos -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                        2
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Liturgia & Voluntários</h3>
                        <p class="text-[10px] text-slate-400 font-medium">Reordene os momentos usando ▲/▼ ou arrastando</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" id="btnGerarAuto" onclick="executarGeracaoEscalaAuto()" class="text-purple-700 hover:text-purple-800 font-extrabold text-xs flex items-center bg-purple-50 hover:bg-purple-100 border border-purple-200 px-3 py-2 rounded-2xl active:scale-95 transition-all shadow-xs gap-1">
                        ⚡ Gerar Escala
                    </button>
                    <button type="button" id="btnAddMomento" class="text-blue-600 hover:text-blue-700 font-extrabold text-xs flex items-center bg-blue-50 hover:bg-blue-100 border border-blue-100/80 px-3 py-2 rounded-2xl active:scale-95 transition-all shadow-xs">
                        <svg class="w-4 h-4 mr-1" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Add
                    </button>
                </div>
            </div>
            
            <div id="momentosList" class="space-y-4">
                <?php 
                $listParaExibir = !empty($atribuicoesExistentes) ? $atribuicoesExistentes : $templates;
                foreach ($listParaExibir as $idx => $mom):
                    $tituloMom = $mom['funcao_id'] ?? ($mom['titulo'] ?? '');
                    $voluntarioIdSalvo = $mom['usuario_id'] ?? null;
                    $isObrigatorio = (strtolower(trim($tituloMom)) === 'estudo / palavra' || strtolower(trim($tituloMom)) === 'estudo');
                ?>
                    <div class="momento-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative cursor-grab transition-all duration-200 hover:border-blue-300" draggable="true">
                        <!-- Controls Header (Reordenação & Exclusão) -->
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-200/50">
                            <div class="flex items-center space-x-2">
                                <span class="drag-handle text-slate-400 hover:text-slate-600 cursor-grab p-1" title="Arrastar para reordenar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                </span>
                                <span class="momento-badge font-extrabold text-xs text-blue-600 bg-blue-100/70 px-2 py-0.5 rounded-lg">#<?= $idx + 1 ?></span>
                                <?php if ($isObrigatorio): ?>
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
                                <?php if (!$isObrigatorio): ?>
                                    <button type="button" aria-label="Remover momento" class="remove-momento text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label data-field-for="titulo" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Momento da Liturgia</label>
                                <select data-field="titulo" name="momentos[<?= $idx ?>][titulo]" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800">
                                    <?php foreach ($templates as $t): ?>
                                        <option value="<?= SecurityHelper::e($t['titulo']) ?>" <?= ($t['titulo'] === $tituloMom) ? 'selected' : '' ?>>
                                            <?= SecurityHelper::e($t['titulo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if (!in_array($tituloMom, array_column($templates, 'titulo'))): ?>
                                        <option value="<?= SecurityHelper::e($tituloMom) ?>" selected><?= SecurityHelper::e($tituloMom) ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label data-field-for="voluntario_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Membro Responsável</label>
                                <select data-field="voluntario_id" name="momentos[<?= $idx ?>][voluntario_id]" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                                    <option value="">Selecione o responsável...</option>
                                    <?php if (!empty($voluntarios) && is_array($voluntarios)): ?>
                                        <?php foreach ($voluntarios as $vol): ?>
                                            <option value="<?= (int)$vol['id'] ?>" <?= ((int)$vol['id'] === (int)$voluntarioIdSalvo) ? 'selected' : '' ?>>
                                                <?= SecurityHelper::e($vol['nome']) ?> (<?= SecurityHelper::e($vol['perfil'] ?? 'Membro') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" id="btnSalvarEscala" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-98 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition-all text-sm tracking-wide mt-2">
            Salvar Alterações da Liturgia
        </button>
    </form>
</div>

<!-- Script de Reordenação e Adição Dinâmica -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnAddMomento = document.getElementById('btnAddMomento');
        const momentosList = document.getElementById('momentosList');

        const templateOptionsHtml = `
            <?php foreach ($templates as $t): ?>
                <option value="<?= SecurityHelper::e($t['titulo']) ?>"><?= SecurityHelper::e($t['titulo']) ?></option>
            <?php endforeach; ?>
        `;

        const voluntarioOptionsHtml = `
            <option value="">Selecione o responsável...</option>
            <?php if (!empty($voluntarios) && is_array($voluntarios)): ?>
                <?php foreach ($voluntarios as $vol): ?>
                    <option value="<?= (int)$vol['id'] ?>"><?= SecurityHelper::e($vol['nome']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        `;

        function reindexMomentos() {
            const items = momentosList.querySelectorAll('.momento-item');
            items.forEach((item, index) => {
                const badge = item.querySelector('.momento-badge');
                if (badge) badge.textContent = `#${index + 1}`;

                const fields = item.querySelectorAll('[data-field]');
                fields.forEach(input => {
                    const fieldName = input.getAttribute('data-field');
                    input.name = `momentos[${index}][${fieldName}]`;
                    input.id = `momento_${fieldName}_${index}`;
                });

                const labels = item.querySelectorAll('[data-field-for]');
                labels.forEach(label => {
                    const fieldFor = label.getAttribute('data-field-for');
                    label.htmlFor = `momento_${fieldFor}_${index}`;
                });
            });
        }

        btnAddMomento.addEventListener('click', () => {
            const nextIndex = momentosList.children.length;
            const template = `
                <div class="momento-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative cursor-grab transition-all duration-200 hover:border-blue-300" draggable="true">
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
                            <label data-field-for="titulo" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Momento da Liturgia</label>
                            <select data-field="titulo" name="momentos[${nextIndex}][titulo]" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800">
                                ${templateOptionsHtml}
                            </select>
                        </div>
                        <div>
                            <label data-field-for="voluntario_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Membro Responsável</label>
                            <select data-field="voluntario_id" name="momentos[${nextIndex}][voluntario_id]" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-medium text-slate-700">
                                ${voluntarioOptionsHtml}
                            </select>
                        </div>
                    </div>
                </div>
            `;
            momentosList.insertAdjacentHTML('beforeend', template);
            reindexMomentos();
        });

        // Delegação de cliques para botões subir, descer e remover
        momentosList.addEventListener('click', (e) => {
            const btnMoveUp = e.target.closest('.btn-move-up');
            const btnMoveDown = e.target.closest('.btn-move-down');
            const btnRemove = e.target.closest('.remove-momento');

            if (btnMoveUp) {
                const item = btnMoveUp.closest('.momento-item');
                const prev = item.previousElementSibling;
                if (prev) {
                    momentosList.insertBefore(item, prev);
                    reindexMomentos();
                }
            } else if (btnMoveDown) {
                const item = btnMoveDown.closest('.momento-item');
                const next = item.nextElementSibling;
                if (next) {
                    momentosList.insertBefore(next, item);
                    reindexMomentos();
                }
            } else if (btnRemove) {
                const item = btnRemove.closest('.momento-item');
                if (item) {
                    item.remove();
                    reindexMomentos();
                }
            }
        });

        // Suporte Drag and Drop HTML5
        let draggedItem = null;

        momentosList.addEventListener('dragstart', (e) => {
            draggedItem = e.target.closest('.momento-item');
            if (draggedItem) {
                e.dataTransfer.effectAllowed = 'move';
                draggedItem.classList.add('opacity-50', 'bg-blue-50');
            }
        });

        momentosList.addEventListener('dragend', (e) => {
            if (draggedItem) {
                draggedItem.classList.remove('opacity-50', 'bg-blue-50');
                draggedItem = null;
                reindexMomentos();
            }
        });

        momentosList.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const targetItem = e.target.closest('.momento-item');
            if (targetItem && targetItem !== draggedItem) {
                const rect = targetItem.getBoundingClientRect();
                const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
                momentosList.insertBefore(draggedItem, next ? targetItem.nextSibling : targetItem);
            }
        });
    });

    async function executarGeracaoEscalaAuto() {
        const momentosItems = document.querySelectorAll('.momento-item');
        if (momentosItems.length === 0) return;

        const btn = document.getElementById('btnGerarAuto');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '⏳ Gerando...';
        }

        const momentosPayload = [];
        momentosItems.forEach((item, index) => {
            const tituloElement = item.querySelector('[name*="[titulo]"]');
            const tituloVal = tituloElement ? tituloElement.value : '';
            
            momentosPayload.push({
                idx: index,
                titulo: tituloVal
            });
        });

        try {
            const response = await fetch('/escala/gerar-automatica', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ momentos: momentosPayload })
            });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (jsonErr) {
                console.error("Resposta do servidor não é JSON:", text);
                showCustomAlert("Ocorreu uma falha no servidor ao gerar a escala.", "Erro no Servidor", "erro");
                return;
            }

            if (data.success && data.atribuicoes) {
                data.atribuicoes.forEach((attr) => {
                    const item = momentosItems[attr.idx];
                    if (item) {
                        const select = item.querySelector('select[name*="[voluntario_id]"]');
                        if (select && attr.usuario_id) {
                            select.value = attr.usuario_id;
                            select.classList.add('ring-2', 'ring-purple-500', 'bg-purple-50');
                            setTimeout(() => select.classList.remove('ring-2', 'ring-purple-500', 'bg-purple-50'), 1500);
                        }
                    }
                });
            } else if (data.message) {
                showCustomAlert(data.message, "Aviso da Escala", "erro");
            }
        } catch (err) {
            console.error("Erro ao gerar escala automática:", err);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '⚡ Gerar Escala';
            }
        }
    }

    // Validação estrita de Datas (Retroativas e Duplicadas)
    const datasExistentes = <?= json_encode($datasExistentes ?? []) ?>;
    const todayStr = '<?= date('Y-m-d') ?>';

    function validarDataCulto() {
        const dateInput = document.getElementById('data_culto');
        const submitBtn = document.getElementById('btnSalvarEscala');
        const errorDiv = document.getElementById('dateErrorMsg');

        if (!dateInput || !submitBtn) return;

        const selectedDate = dateInput.value;
        let errorMsg = '';

        if (selectedDate && selectedDate < todayStr) {
            errorMsg = '⚠️ Não é possível alterar eventos para datas anteriores a hoje.';
        } else if (selectedDate && datasExistentes.includes(selectedDate)) {
            const partes = selectedDate.split('-');
            const dataFmt = `${partes[2]}/${partes[1]}/${partes[0]}`;
            errorMsg = `⚠️ Já existe outro evento cadastrado para a data ${dataFmt}. Selecione outra data.`;
        }

        if (errorMsg) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            submitBtn.classList.remove('hover:from-blue-700', 'hover:to-indigo-700', 'active:scale-98');
            if (errorDiv) {
                errorDiv.textContent = errorMsg;
                errorDiv.classList.remove('hidden');
            }
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            submitBtn.classList.add('hover:from-blue-700', 'hover:to-indigo-700', 'active:scale-98');
            if (errorDiv) {
                errorDiv.classList.add('hidden');
                errorDiv.textContent = '';
            }
        }
    }

    const dateInputEl = document.getElementById('data_culto');
    if (dateInputEl) {
        dateInputEl.addEventListener('change', validarDataCulto);
        dateInputEl.addEventListener('input', validarDataCulto);
        validarDataCulto();
    }
</script>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
