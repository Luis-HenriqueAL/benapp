<?php 
/**
 * View: Parametrização das Informações da Célula
 * 
 * Interface moderna no estado da arte (Web-Mobile UI) para configuração do perfil da célula:
 * Dados gerais, endereço com busca automática via API ViaCEP, anfitriões (N nomes, 2 telefones)
 * e líderes (N nomes, N telefones).
 * 
 * @var array|null $celula Dados parametrizados da célula logada.
 */

require_once __DIR__ . '/../../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;
ob_start(); 
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>
<div class="space-y-5 max-w-md mx-auto pb-6">
    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center space-x-3.5">
        <a href="/" class="p-2.5 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-100 transition-all active:scale-90 flex items-center justify-center shrink-0" aria-label="Voltar para início">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Informações da Célula</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Configure os dados de endereço e liderança</p>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= SecurityHelper::e($flashSuccess) ?>
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 font-bold">&times;</button>
        </div>
    <?php endif; ?>

    <form id="celulaForm" action="/celula/update" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">

        <!-- 1. Dados Básicos da Célula -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-4">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-3 mb-1">
                <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                    1
                </div>
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Dados Principais</h3>
            </div>

            <div>
                <label for="nome_celula" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nome da Célula</label>
                <input id="nome_celula" type="text" name="nome_celula" value="<?= SecurityHelper::e($celula['nome_celula'] ?? '') ?>" required placeholder="Ex: Célula Boas Novas" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="dia_semana" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Dia da Semana</label>
                    <select id="dia_semana" name="dia_semana" required class="w-full px-3.5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all appearance-none">
                        <?php 
                        $dias = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo'];
                        $diaAtual = $celula['dia_semana'] ?? 'Quarta-feira';
                        foreach ($dias as $d):
                        ?>
                            <option value="<?= $d ?>" <?= $diaAtual === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="horario" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Horário</label>
                    <input id="horario" type="time" name="horario" value="<?= SecurityHelper::e($celula['horario'] ?? '19:30') ?>" required class="w-full px-3.5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                </div>
            </div>
        </div>

        <!-- 2. Endereço com Integração ViaCEP -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-4">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-3 mb-1">
                <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                    2
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Endereço da Reunião</h3>
                    <p class="text-[10px] text-slate-400 font-medium">Digite o CEP para buscar automaticamente</p>
                </div>
            </div>

            <div>
                <label for="cep" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">CEP</label>
                <div class="relative">
                    <input id="cep" type="text" name="cep" value="<?= SecurityHelper::e($celula['cep'] ?? '') ?>" placeholder="00000-000" maxlength="9" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                    <span id="cepLoader" class="hidden absolute right-3.5 top-3.5 text-blue-600">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                </div>
            </div>

            <div>
                <label for="logradouro" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Rua / Logradouro</label>
                <input id="logradouro" type="text" name="logradouro" value="<?= SecurityHelper::e($celula['logradouro'] ?? '') ?>" placeholder="Ex: Rua das Flores" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="numero" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Número</label>
                    <input id="numero" type="text" name="numero" value="<?= SecurityHelper::e($celula['numero'] ?? '') ?>" placeholder="Ex: 123" class="w-full px-3.5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                </div>
                <div>
                    <label for="complemento" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Complemento</label>
                    <input id="complemento" type="text" name="complemento" value="<?= SecurityHelper::e($celula['complemento'] ?? '') ?>" placeholder="Ex: Apto 12" class="w-full px-3.5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div class="col-span-1">
                    <label for="bairro" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bairro</label>
                    <input id="bairro" type="text" name="bairro" value="<?= SecurityHelper::e($celula['bairro'] ?? '') ?>" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div class="col-span-1">
                    <label for="cidade" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Cidade</label>
                    <input id="cidade" type="text" name="cidade" value="<?= SecurityHelper::e($celula['cidade'] ?? '') ?>" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div class="col-span-1">
                    <label for="estado" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">UF</label>
                    <input id="estado" type="text" name="estado" value="<?= SecurityHelper::e($celula['estado'] ?? '') ?>" placeholder="SP" maxlength="2" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-medium uppercase text-center focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <!-- 3. Anfitriões (N nomes, até 2 telefones cada) -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-1">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                        3
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Anfitrião(ões)</h3>
                </div>
                <button type="button" id="btnAddAnfitriao" class="text-blue-600 hover:text-blue-700 font-extrabold text-xs flex items-center bg-blue-50 hover:bg-blue-100 border border-blue-100/80 px-3 py-1.5 rounded-2xl active:scale-95 transition-all shadow-xs">
                    + Anfitrião
                </button>
            </div>

            <div id="anfitrioesList" class="space-y-4">
                <?php 
                $anfitrioes = $celula['anfitrioes'] ?? [['nome' => '', 'telefone1' => '', 'telefone2' => '']];
                if (empty($anfitrioes)) $anfitrioes = [['nome' => '', 'telefone1' => '', 'telefone2' => '']];
                foreach ($anfitrioes as $idx => $anf):
                ?>
                    <div class="anfitriao-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative">
                        <?php if ($idx > 0): ?>
                            <button type="button" aria-label="Remover anfitrião" class="remove-item absolute -top-2.5 -right-2.5 bg-rose-100 hover:bg-rose-200 text-rose-600 rounded-full p-1.5 shadow-sm active:scale-90 transition border border-rose-200">
                                <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        <?php endif; ?>
                        <div class="space-y-3">
                            <div>
                                <label for="anf_nome_<?= $idx ?>" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nome do Anfitrião</label>
                                <input id="anf_nome_<?= $idx ?>" type="text" name="anfitrioes[<?= $idx ?>][nome]" value="<?= SecurityHelper::e($anf['nome'] ?? '') ?>" placeholder="Ex: Família Santos" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="anf_tel1_<?= $idx ?>" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Telefone 1</label>
                                    <input id="anf_tel1_<?= $idx ?>" type="text" name="anfitrioes[<?= $idx ?>][telefone1]" value="<?= SecurityHelper::e($anf['telefone1'] ?? '') ?>" placeholder="(11) 98888-0000" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
                                </div>
                                <div>
                                    <label for="anf_tel2_<?= $idx ?>" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Telefone 2 (opcional)</label>
                                    <input id="anf_tel2_<?= $idx ?>" type="text" name="anfitrioes[<?= $idx ?>][telefone2]" value="<?= SecurityHelper::e($anf['telefone2'] ?? '') ?>" placeholder="(11) 97777-0000" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4. Líderes (N nomes, N telefones cada) -->
        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-1">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-2xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100/80 shadow-xs">
                        4
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-sm tracking-tight">Líder(es) da Célula</h3>
                </div>
                <button type="button" id="btnAddLider" class="text-blue-600 hover:text-blue-700 font-extrabold text-xs flex items-center bg-blue-50 hover:bg-blue-100 border border-blue-100/80 px-3 py-1.5 rounded-2xl active:scale-95 transition-all shadow-xs">
                    + Líder
                </button>
            </div>

            <div id="lideresList" class="space-y-4">
                <?php 
                $lideres = $celula['lideres'] ?? [['nome' => '', 'telefones' => ['']]];
                if (empty($lideres)) $lideres = [['nome' => '', 'telefones' => ['']]];
                foreach ($lideres as $lidx => $lid):
                ?>
                    <div class="lider-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative">
                        <?php if ($lidx > 0): ?>
                            <button type="button" aria-label="Remover líder" class="remove-item absolute -top-2.5 -right-2.5 bg-rose-100 hover:bg-rose-200 text-rose-600 rounded-full p-1.5 shadow-sm active:scale-90 transition border border-rose-200">
                                <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        <?php endif; ?>
                        <div class="space-y-3">
                            <div>
                                <label for="lid_nome_<?= $lidx ?>" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nome do Líder</label>
                                <input id="lid_nome_<?= $lidx ?>" type="text" name="lideres[<?= $lidx ?>][nome]" value="<?= SecurityHelper::e($lid['nome'] ?? '') ?>" placeholder="Ex: João da Silva" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800">
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Telefone(s)</label>
                                <?php 
                                $tels = $lid['telefones'] ?? [''];
                                if (empty($tels)) $tels = [''];
                                foreach ($tels as $tidx => $tel):
                                ?>
                                    <input type="text" name="lideres[<?= $lidx ?>][telefones][]" value="<?= SecurityHelper::e($tel) ?>" placeholder="(11) 99999-0000" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 mb-2">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-98 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition-all text-sm tracking-wide mt-2">
            Salvar Informações da Célula
        </button>
    </form>
</div>

<!-- Script de Integração ViaCEP e Manipulação Dinâmica -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Integration ViaCEP
        const cepInput = document.getElementById('cep');
        const loader = document.getElementById('cepLoader');

        const searchCep = async (cepVal) => {
            const cepClean = cepVal.replace(/\D/g, '');
            if (cepClean.length === 8) {
                loader.classList.remove('hidden');
                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cepClean}/json/`);
                    const data = await response.json();
                    if (!data.erro) {
                        document.getElementById('logradouro').value = data.logradouro || '';
                        document.getElementById('bairro').value = data.bairro || '';
                        document.getElementById('cidade').value = data.localidade || '';
                        document.getElementById('estado').value = data.uf || '';
                    }
                } catch (e) {
                    console.warn('Erro ao consultar ViaCEP', e);
                } finally {
                    loader.classList.add('hidden');
                }
            }
        };

        cepInput.addEventListener('blur', (e) => searchCep(e.target.value));
        cepInput.addEventListener('keyup', (e) => {
            if (e.target.value.replace(/\D/g, '').length === 8) {
                searchCep(e.target.value);
            }
        });

        // Dynamic Anfitriões
        const btnAddAnfitriao = document.getElementById('btnAddAnfitriao');
        const anfitrioesList = document.getElementById('anfitrioesList');
        let anfIndex = <?= count($anfitrioes) ?>;

        btnAddAnfitriao.addEventListener('click', () => {
            const tpl = `
                <div class="anfitriao-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative mt-4">
                    <button type="button" aria-label="Remover anfitrião" class="remove-item absolute -top-2.5 -right-2.5 bg-rose-100 hover:bg-rose-200 text-rose-600 rounded-full p-1.5 shadow-sm active:scale-90 transition border border-rose-200">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nome do Anfitrião</label>
                            <input type="text" name="anfitrioes[${anfIndex}][nome]" placeholder="Ex: Família Lima" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Telefone 1</label>
                                <input type="text" name="anfitrioes[${anfIndex}][telefone1]" placeholder="(11) 98888-0000" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Telefone 2 (opcional)</label>
                                <input type="text" name="anfitrioes[${anfIndex}][telefone2]" placeholder="(11) 97777-0000" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            anfitrioesList.insertAdjacentHTML('beforeend', tpl);
            anfIndex++;
        });

        // Dynamic Líderes
        const btnAddLider = document.getElementById('btnAddLider');
        const lideresList = document.getElementById('lideresList');
        let lidIndex = <?= count($lideres) ?>;

        btnAddLider.addEventListener('click', () => {
            const tpl = `
                <div class="lider-item p-4 border border-slate-200/80 rounded-2xl bg-slate-50/70 relative mt-4">
                    <button type="button" aria-label="Remover líder" class="remove-item absolute -top-2.5 -right-2.5 bg-rose-100 hover:bg-rose-200 text-rose-600 rounded-full p-1.5 shadow-sm active:scale-90 transition border border-rose-200">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nome do Líder</label>
                            <input type="text" name="lideres[${lidIndex}][nome]" placeholder="Ex: Maria Alves" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 outline-none text-xs font-semibold text-slate-800">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Telefone(s)</label>
                            <input type="text" name="lideres[${lidIndex}][telefones][]" placeholder="(11) 99999-0000" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 mb-2">
                        </div>
                    </div>
                </div>
            `;
            lideresList.insertAdjacentHTML('beforeend', tpl);
            lidIndex++;
        });

        // Removal delegation
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-item');
            if (btn) {
                const item = btn.closest('.anfitriao-item, .lider-item');
                if (item) item.remove();
            }
        });
    });
</script>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
