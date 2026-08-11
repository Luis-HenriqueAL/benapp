<?php 
/**
 * View: Tela de Autenticação / Login
 * 
 * Interface de login no estado da arte para dispositivos móveis (Web-Mobile UI),
 * apresentando topo curvado em degradê azul, card flutuante sanitizado com formulário e suporte a CSRF.
 */

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - BenApp</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent; 
        }
    </style>
</head>
<body class="bg-slate-50 min-h-full flex flex-col justify-between antialiased selection:bg-blue-600 selection:text-white">

    <!-- Header com Curva e Gradiente Azul -->
    <div class="relative bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-800 text-white pt-14 pb-28 px-6 rounded-b-[44px] shadow-2xl text-center overflow-hidden">
        <!-- Detalhes de luz no fundo -->
        <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-indigo-400/20 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Logo Ícone Flutuante -->
        <div class="relative z-10 mx-auto w-22 h-22 bg-white/15 backdrop-blur-xl rounded-3xl flex items-center justify-center p-3 border border-white/25 shadow-xl mb-4 transform hover:scale-105 transition-transform duration-300">
            <div class="w-16 h-16 bg-white text-blue-600 rounded-2xl flex items-center justify-center font-black text-2xl shadow-md">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>

        <h1 class="relative z-10 text-3xl font-extrabold tracking-tight">Gestão de Células</h1>
        <p class="relative z-10 text-blue-100/90 text-xs font-medium mt-1 tracking-wide">BenApp - Entre para acessar suas escalas</p>
    </div>

    <!-- Card de Formulário Flutuante -->
    <div class="flex-1 px-6 -mt-20 relative z-20 max-w-md mx-auto w-full">
        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/80 p-7 sm:p-9 border border-slate-100/80">
            <h2 class="text-xl font-extrabold text-slate-800 text-center mb-6 tracking-tight">Acesse sua Conta</h2>

            <?php if ($error): ?>
                <div class="mb-5 bg-red-50 border border-red-200 p-3.5 rounded-2xl text-red-700 text-xs font-semibold flex items-center justify-between shadow-xs">
                    <span><?= htmlspecialchars($error) ?></span>
                    <button onclick="this.parentElement.remove()" class="text-red-500 font-bold ml-2 hover:text-red-700">&times;</button>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= \Helpers\SecurityHelper::generateCsrfToken() ?>">

                <div>
                    <label for="email" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">E-mail</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </span>
                        <input id="email" type="email" name="email" value="admin@celula.com" required placeholder="seu@email.com" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Senha</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input id="password" type="password" name="password" value="senha123" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-98 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition-all duration-200 text-sm tracking-wide mt-3">
                    Entrar no Sistema
                </button>
            </form>

            <div class="mt-6 text-center border-t border-slate-100 pt-4">
                <p class="text-[11px] font-medium text-slate-400">Credenciais de teste:</p>
                <p class="text-xs font-bold text-blue-600 mt-0.5">E-mail: admin@celula.com | Senha: senha123</p>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="py-6 text-center text-xs font-medium text-slate-400">
        BenApp &copy; <?= date('Y') ?> - Todos os direitos reservados
    </div>

</body>
</html>
