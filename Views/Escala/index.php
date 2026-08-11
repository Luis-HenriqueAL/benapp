<?php ob_start(); ?>
<div class="space-y-4 max-w-lg mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Escalas</h2>
            <p class="text-sm text-gray-500 font-medium mt-1">Sua agenda de cultos e eventos</p>
        </div>
    </div>

    <!-- Filtros (Exemplo) -->
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <span class="px-4 py-1.5 bg-blue-600 text-white text-sm font-semibold rounded-full whitespace-nowrap shadow-sm">Próximas</span>
        <span class="px-4 py-1.5 bg-white text-gray-600 border border-gray-200 text-sm font-medium rounded-full whitespace-nowrap">Passadas</span>
        <span class="px-4 py-1.5 bg-white text-gray-600 border border-gray-200 text-sm font-medium rounded-full whitespace-nowrap">Meus Turnos</span>
    </div>

    <!-- Card de Escala: Culto de Domingo -->
    <a href="/escala/1" class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 active:scale-[0.98] transition-transform">
        <div class="flex justify-between items-start mb-3">
            <div>
                <span class="inline-block px-2.5 py-1 bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wide rounded mb-2">Culto de Domingo</span>
                <h3 class="text-xl font-bold text-gray-900">16 Ago, 18:00</h3>
            </div>
            <div class="text-right flex flex-col items-end">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Voluntários</span>
                <span class="text-lg font-bold text-blue-600">5</span>
            </div>
        </div>
        
        <p class="text-sm text-gray-600 font-medium flex items-center mb-4">
            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Líder: João Silva
        </p>

        <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
            <div class="flex items-center">
                <!-- Avatares -->
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold ring-2 ring-white">JS</div>
                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold ring-2 ring-white -ml-2">MA</div>
                <div class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center text-xs font-bold ring-2 ring-white -ml-2">PT</div>
                <div class="text-xs font-medium text-gray-400 ml-2">+2 confirmados</div>
            </div>
            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </div>
    </a>

    <!-- Card de Escala: Reunião de Jovens -->
    <a href="/escala/2" class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 active:scale-[0.98] transition-transform">
        <div class="flex justify-between items-start mb-3">
            <div>
                <span class="inline-block px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-bold uppercase tracking-wide rounded mb-2">Reunião de Jovens</span>
                <h3 class="text-xl font-bold text-gray-900">22 Ago, 20:00</h3>
            </div>
            <div class="text-right flex flex-col items-end">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Voluntários</span>
                <span class="text-lg font-bold text-blue-600">3</span>
            </div>
        </div>
        
        <p class="text-sm text-gray-600 font-medium flex items-center mb-4">
            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Líder: Maria Alves
        </p>

        <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
            <div class="flex items-center">
                <div class="text-xs font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded border border-yellow-100">Faltam voluntários</div>
            </div>
            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </div>
    </a>
</div>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
