<?php 
/**
 * View: Dashboard / Lista de Escalas
 * 
 * Exibe a relação de escalas de cultos e eventos com contagem de voluntários,
 * avatar dos participantes e indicação visual de status de preenchimento.
 * Interface mobile-first no estado da arte.
 */
ob_start(); 
?>
<div class="space-y-5 max-w-md mx-auto">
    <!-- Header da Seção -->
    <div class="bg-white rounded-3xl p-5 shadow-xl shadow-slate-200/50 border border-slate-100 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Escalas & Cultos</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Agenda de cultos e eventos da célula</p>
        </div>
        <a href="/escala/create" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 text-white font-extrabold px-4 py-2.5 rounded-2xl shadow-md shadow-blue-500/20 text-xs transition-all flex items-center gap-1.5 tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Nova
        </a>
    </div>

    <!-- Filtros em Pills Horizontal -->
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
        <button class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-2xl shadow-sm whitespace-nowrap active:scale-95 transition-all">Próximas</button>
        <button class="px-4 py-2 bg-white text-slate-600 border border-slate-200/80 text-xs font-bold rounded-2xl whitespace-nowrap hover:bg-slate-50 active:scale-95 transition-all">Passadas</button>
        <button class="px-4 py-2 bg-white text-slate-600 border border-slate-200/80 text-xs font-bold rounded-2xl whitespace-nowrap hover:bg-slate-50 active:scale-95 transition-all">Meus Turnos</button>
    </div>

    <!-- Lista de Escalas em Cards Rounded-3XL -->
    <div class="space-y-3.5">
        <!-- Card 1: Culto de Domingo -->
        <a href="/escala/1" class="block bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 p-5 active:scale-[0.98] transition-all group">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/60 text-[10px] font-extrabold uppercase tracking-wider rounded-full mb-2">
                        Culto de Domingo
                    </span>
                    <h3 class="text-lg font-extrabold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors">16 Ago, 18:00</h3>
                </div>
                <div class="text-right flex flex-col items-end">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-0.5">Voluntários</span>
                    <span class="text-base font-black text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-xl border border-blue-100">5</span>
                </div>
            </div>
            
            <p class="text-xs text-slate-500 font-medium flex items-center mb-4">
                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Líder: <strong class="text-slate-700 ml-1">João Silva</strong>
            </p>

            <div class="pt-3.5 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-bold ring-2 ring-white shadow-xs">JS</div>
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center text-xs font-bold ring-2 ring-white shadow-xs -ml-2">MA</div>
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-xs font-bold ring-2 ring-white shadow-xs -ml-2">PT</div>
                    <span class="text-xs font-bold text-slate-400 ml-2.5">+2 escalados</span>
                </div>
                <div class="w-8 h-8 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </div>
        </a>

        <!-- Card 2: Reunião de Jovens -->
        <a href="/escala/2" class="block bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 p-5 active:scale-[0.98] transition-all group">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <span class="inline-flex items-center px-3 py-1 bg-purple-50 text-purple-700 border border-purple-200/60 text-[10px] font-extrabold uppercase tracking-wider rounded-full mb-2">
                        Reunião de Jovens
                    </span>
                    <h3 class="text-lg font-extrabold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors">22 Ago, 20:00</h3>
                </div>
                <div class="text-right flex flex-col items-end">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-0.5">Voluntários</span>
                    <span class="text-base font-black text-amber-600 bg-amber-50 px-2.5 py-0.5 rounded-xl border border-amber-100">3</span>
                </div>
            </div>
            
            <p class="text-xs text-slate-500 font-medium flex items-center mb-4">
                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Líder: <strong class="text-slate-700 ml-1">Maria Alves</strong>
            </p>

            <div class="pt-3.5 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center">
                    <span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200/60 text-[10px] font-extrabold rounded-full tracking-wide">
                        ⚠️ Faltam voluntários
                    </span>
                </div>
                <div class="w-8 h-8 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </div>
        </a>
    </div>
</div>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
