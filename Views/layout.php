<?php 
/**
 * Layout Principal do Sistema (Base Template)
 * 
 * Interface moderna de alta fidelidade (State-of-the-Art Web-Mobile UI).
 * Possui navegação lateral retrátil (Sidebar Drawer), cabeçalho responsivo em gradiente azul,
 * sistema de notificações de erro amigáveis e navegação inferior em estilo Dock flutuante.
 *
 * @var string|null $content Conteúdo dinâmico renderizado pelas views filhas.
 */

require_once __DIR__ . '/../Helpers/SecurityHelper.php';
use Helpers\SecurityHelper;

$currentUser = $_SESSION['user'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentUri = str_replace('/public', '', $currentUri);
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>BenApp - Gestão de Células</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent; 
            overscroll-behavior-y: none;
        }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-full flex flex-col justify-between relative overflow-x-hidden selection:bg-brand-500 selection:text-white">
    
    <!-- Backdrop Overlay para o Sidebar Drawer -->
    <div id="sidebarBackdrop" onclick="closeSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 opacity-0 pointer-events-none transition-opacity duration-300"></div>

    <!-- Sidebar Drawer (Menu Lateral Retrátil Estado da Arte) -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-80 bg-white z-50 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-out flex flex-col justify-between rounded-r-3xl border-r border-slate-100">
        <div>
            <!-- Header do Sidebar com perfil do usuário -->
            <div class="bg-gradient-to-br from-brand-600 via-blue-600 to-indigo-700 text-white p-6 relative overflow-hidden rounded-tr-3xl">
                <!-- Círculos decorativos -->
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                
                <button onclick="closeSidebar()" class="absolute top-4 right-4 text-white/80 hover:text-white p-2 rounded-full hover:bg-white/10 transition-all active:scale-90" aria-label="Fechar menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="flex items-center space-x-3.5 mt-2">
                    <div class="w-13 h-13 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30 text-white font-extrabold text-xl shadow-inner">
                        <?= htmlspecialchars(substr($currentUser['nome'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-base tracking-tight leading-snug"><?= htmlspecialchars($currentUser['nome'] ?? 'Usuário') ?></h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white border border-white/20 mt-1 uppercase tracking-wider">
                            <?= htmlspecialchars($currentUser['perfil'] ?? 'Membro') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navegação Principal no Sidebar -->
            <nav class="p-4 space-y-1.5">
                <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Navegação Principal</div>

                <a href="/" class="flex items-center space-x-3.5 px-4 py-3.5 rounded-2xl font-semibold text-sm transition-all duration-200 <?= ($currentUri === '/' || $currentUri === '' || $currentUri === '/escala') ? 'bg-brand-50 text-brand-600 font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                    <svg class="w-5 h-5 <?= ($currentUri === '/' || $currentUri === '' || $currentUri === '/escala') ? 'text-brand-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Escalas & Cultos</span>
                </a>

                <a href="/escala/create" class="flex items-center space-x-3.5 px-4 py-3.5 rounded-2xl font-semibold text-sm transition-all duration-200 <?= ($currentUri === '/escala/create') ? 'bg-brand-50 text-brand-600 font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                    <svg class="w-5 h-5 <?= ($currentUri === '/escala/create') ? 'text-brand-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Nova Escala</span>
                </a>

                <a href="/usuarios" class="flex items-center space-x-3.5 px-4 py-3.5 rounded-2xl font-semibold text-sm transition-all duration-200 <?= (strpos($currentUri, '/usuarios') === 0) ? 'bg-brand-50 text-brand-600 font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                    <svg class="w-5 h-5 <?= (strpos($currentUri, '/usuarios') === 0) ? 'text-brand-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Equipe & Usuários</span>
                </a>
            </nav>
        </div>

        <!-- Footer do Sidebar -->
        <div class="p-4 border-t border-slate-100">
            <a href="/logout" class="flex items-center space-x-3.5 px-4 py-3.5 rounded-2xl font-bold text-sm text-red-600 hover:bg-red-50 transition-all active:scale-98">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Sair da Conta</span>
            </a>
        </div>
    </aside>

    <!-- Cabeçalho Topo com Ondas e Gradiente -->
    <header class="bg-gradient-to-br from-brand-700 via-brand-600 to-indigo-700 text-white pt-6 pb-14 px-5 rounded-b-[38px] shadow-xl relative overflow-hidden z-10">
        <div class="max-w-md mx-auto flex justify-between items-center relative z-10">
            <!-- Botão Hambúrguer -->
            <button onclick="openSidebar()" class="p-2.5 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-md transition-all active:scale-90 flex items-center justify-center" aria-label="Abrir menu lateral">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Título Central -->
            <div class="text-center">
                <h1 class="text-xl font-extrabold tracking-tight leading-tight">BenApp</h1>
                <p class="text-[11px] font-medium text-brand-100/90 tracking-wide">Gestão Inteligente de Célula</p>
            </div>

            <!-- Avatar rápido -->
            <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30 text-white font-extrabold text-sm shadow-md">
                <?= htmlspecialchars(substr($currentUser['nome'] ?? 'U', 0, 1)) ?>
            </div>
        </div>
    </header>

    <!-- Modal de Erro Amigável -->
    <?php if (!empty($flashError)): ?>
    <div id="errorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
        <div class="bg-white rounded-3xl p-6 max-w-xs w-full shadow-2xl border border-red-100 text-center transform transition-all">
            <div class="w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-red-100">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 mb-1.5">Aviso do Sistema</h3>
            <p class="text-xs font-medium text-slate-600 mb-5 leading-relaxed"><?= htmlspecialchars($flashError) ?></p>
            <button onclick="document.getElementById('errorModal').remove()" class="w-full bg-brand-600 hover:bg-brand-700 active:scale-95 text-white font-bold py-3 px-6 rounded-2xl shadow-md transition-all text-xs tracking-wide">
                Entendido
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Container Principal de Conteúdo -->
    <main class="flex-1 px-4 -mt-8 pb-28 relative z-20 max-w-md mx-auto w-full">
        <?= $content ?? '' ?>
    </main>

    <!-- Navegação Inferior Dock Flutuante -->
    <nav class="glass-panel border-t border-slate-200/80 fixed bottom-0 left-0 w-full flex justify-around items-center py-2 px-6 z-40 shadow-[0_-8px_30px_rgba(0,0,0,0.06)]">
        <a href="/" class="flex flex-col items-center py-1 <?= ($currentUri === '/' || $currentUri === '' || $currentUri === '/escala') ? 'text-brand-600 font-bold' : 'text-slate-400 hover:text-brand-600' ?> transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-[10px] font-bold uppercase tracking-wider mt-1">Escalas</span>
        </a>

        <a href="/escala/create" class="flex flex-col items-center py-1 group">
            <div class="bg-gradient-to-tr from-brand-600 to-indigo-600 text-white rounded-2xl p-3.5 -mt-8 shadow-xl shadow-brand-500/30 border-4 border-slate-50 group-active:scale-90 transition-transform flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-brand-600 mt-0.5">Nova</span>
        </a>

        <a href="/usuarios" class="flex flex-col items-center py-1 <?= (strpos($currentUri, '/usuarios') === 0) ? 'text-brand-600 font-bold' : 'text-slate-400 hover:text-brand-600' ?> transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span class="text-[10px] font-bold uppercase tracking-wider mt-1">Equipe</span>
        </a>
    </nav>

    <!-- Script de Controle do Sidebar -->
    <script>
        function openSidebar() {
            document.getElementById('sidebar').classList.remove('-translate-x-full');
            const backdrop = document.getElementById('sidebarBackdrop');
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.classList.add('opacity-100');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            const backdrop = document.getElementById('sidebarBackdrop');
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0', 'pointer-events-none');
        }
    </script>
</body>
</html>
