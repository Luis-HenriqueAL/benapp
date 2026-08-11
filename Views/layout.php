<?php 
require_once __DIR__ . '/../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

$currentUser = $_SESSION['user'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>BenApp - Gestão de Células</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { 
            -webkit-tap-highlight-color: transparent; 
            overscroll-behavior-y: none;
        }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Wavy Blue Header with Retractable Side Menu Trigger -->
    <header class="bg-gradient-to-b from-blue-700 via-blue-600 to-indigo-700 text-white pt-6 pb-12 px-5 rounded-b-[36px] shadow-lg relative overflow-hidden z-10">
        <!-- SVG Background Patterns -->
        <div class="absolute top-0 right-0 -mr-12 -mt-12 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="flex justify-between items-center relative z-10">
            <div class="flex items-center space-x-3">
                <!-- Hamburger Button to open Side Menu -->
                <button onclick="toggleSidebar()" aria-label="Abrir Menu" class="p-2 rounded-full bg-white/20 hover:bg-white/30 backdrop-blur-md border border-white/30 text-white transition-all active:scale-95">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border border-white/30 text-white font-bold text-lg shadow-sm">
                    <?= htmlspecialchars(substr($currentUser['nome'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight leading-tight">BenApp</h1>
                    <p class="text-xs text-blue-200"><?= htmlspecialchars($currentUser['nome'] ?? 'Usuário') ?> (<?= htmlspecialchars($currentUser['perfil'] ?? 'Membro') ?>)</p>
                </div>
            </div>
            
            <a href="/logout" title="Sair" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-all border border-white/20 active:scale-95">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </a>
        </div>
    </header>

    <!-- Overlay Layer for Retractable Side Menu -->
    <div id="sidebarBackdrop" onclick="closeSidebar()" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300"></div>

    <!-- Retractable Side Menu Drawer -->
    <aside id="sidebar" class="fixed top-0 left-0 bottom-0 w-72 bg-white z-50 transform -translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col justify-between">
        <div>
            <!-- Sidebar Header -->
            <div class="p-5 bg-gradient-to-r from-blue-700 to-indigo-700 text-white flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border border-white/30 text-white font-bold text-base">
                        <?= htmlspecialchars(substr($currentUser['nome'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold leading-tight"><?= htmlspecialchars($currentUser['nome'] ?? 'Usuário') ?></h2>
                        <p class="text-xs text-blue-200"><?= htmlspecialchars($currentUser['email'] ?? 'usuario@celula.com') ?></p>
                    </div>
                </div>
                <button onclick="closeSidebar()" aria-label="Fechar Menu" class="p-1 rounded-lg text-blue-200 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <a href="/" onclick="closeSidebar()" class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Escalas
                </a>

                <a href="/usuarios" onclick="closeSidebar()" class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Usuários
                </a>

                <a href="/escala/create" onclick="closeSidebar()" class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nova Escala
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-gray-100">
            <a href="/logout" class="flex items-center w-full px-4 py-3 text-sm font-bold text-red-600 rounded-2xl hover:bg-red-50 transition-all">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Sair da Conta
            </a>
            <p class="text-[10px] text-gray-400 text-center mt-3">BenApp v1.0 • Multi-tenant</p>
        </div>
    </aside>

    <!-- Error Modal Component -->
    <?php if (!empty($flashError)): ?>
    <div id="errorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-red-100 text-center transform transition-all">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Ops! Ocorreu um problema</h3>
            <p class="text-sm text-gray-600 mb-6"><?= htmlspecialchars($flashError) ?></p>
            <button onclick="document.getElementById('errorModal').remove()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full shadow-md transition-all active:scale-95 text-sm">
                Entendido
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="flex-1 px-4 -mt-6 pb-24 relative z-20 max-w-md mx-auto w-full">
        <?= $content ?? '' ?>
    </main>

    <!-- Bottom Navigation Bar inspired by design_modelo.jpg -->
    <nav class="bg-white border-t border-gray-100 fixed bottom-0 left-0 w-full flex justify-around items-center py-2 px-4 z-40 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
        <a href="/" class="flex flex-col items-center py-1 text-blue-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-[10px] font-bold uppercase tracking-wider mt-1">Escalas</span>
        </a>

        <a href="/usuarios" class="flex flex-col items-center py-1 text-gray-400 hover:text-blue-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span class="text-[10px] font-bold uppercase tracking-wider mt-1">Usuários</span>
        </a>

        <a href="/escala/create" class="flex flex-col items-center py-1 group">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-full p-3.5 -mt-7 shadow-lg border-4 border-gray-100 group-active:scale-90 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 group-hover:text-blue-600 mt-0.5">Nova</span>
        </a>

        <a href="/logout" class="flex flex-col items-center py-1 text-gray-400 hover:text-red-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            <span class="text-[10px] font-bold uppercase tracking-wider mt-1">Sair</span>
        </a>
    </nav>

    <!-- Side Menu Toggle JS -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
            } else {
                closeSidebar();
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
    </script>
</body>
</html>
