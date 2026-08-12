<?php 
/**
 * View: Tela de Entrada para Visitantes via Código
 * 
 * Interface para que visitantes acessem a liturgia/escala da célula informando seu código de acesso.
 */

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Acesso de Visitante - BenApp</title>
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
<body class="bg-slate-50 min-h-full flex flex-col justify-between antialiased selection:bg-purple-600 selection:text-white">

    <!-- Header com Curva e Gradiente Roxo/Azul -->
    <div class="relative bg-gradient-to-br from-purple-700 via-indigo-700 to-blue-800 text-white pt-14 pb-28 px-6 rounded-b-[44px] shadow-2xl text-center overflow-hidden">
        <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-purple-400/20 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Logo Ícone Flutuante -->
        <div class="relative z-10 mx-auto w-22 h-22 bg-white/15 backdrop-blur-xl rounded-3xl flex items-center justify-center p-3 border border-white/25 shadow-xl mb-4 transform hover:scale-105 transition-transform duration-300">
            <div class="w-16 h-16 bg-white text-purple-700 rounded-2xl flex items-center justify-center font-black text-2xl shadow-md">
                🎟️
            </div>
        </div>

        <h1 class="relative z-10 text-3xl font-extrabold tracking-tight">Bem-vindo, Visitante!</h1>
        <p class="relative z-10 text-purple-100/90 text-xs font-medium mt-1 tracking-wide">BenApp - Digite seu código para ver a programação da célula</p>
    </div>

    <!-- Card de Formulário Flutuante -->
    <div class="flex-1 px-6 -mt-20 relative z-20 max-w-md mx-auto w-full">
        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/80 p-7 sm:p-9 border border-slate-100/80">
            <h2 class="text-xl font-extrabold text-slate-800 text-center mb-2 tracking-tight">Digite seu Código</h2>
            <p class="text-xs font-medium text-slate-400 text-center mb-6">Informe o código que você recebeu do líder ou membro da célula.</p>

            <?php if ($error): ?>
                <div class="mb-5 bg-red-50 border border-red-200 p-3.5 rounded-2xl text-red-700 text-xs font-semibold flex items-center justify-between shadow-xs">
                    <span><?= htmlspecialchars($error) ?></span>
                    <button onclick="this.parentElement.remove()" class="text-red-500 font-bold ml-2 hover:text-red-700">&times;</button>
                </div>
            <?php endif; ?>

            <form action="/visitante/acessar" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= \Helpers\SecurityHelper::generateCsrfToken() ?>">

                <div>
                    <label for="codigo_acesso" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 text-center">Código do Visitante</label>
                    <div class="relative">
                        <input id="codigo_acesso" type="text" name="codigo_acesso" required 
                               placeholder="Ex: V8K2P9" 
                               maxlength="10"
                               autocomplete="off"
                               style="text-transform: uppercase;"
                               oninput="this.value = this.value.toUpperCase()"
                               class="w-full text-center tracking-widest text-2xl font-black py-4 bg-purple-50/50 border-2 border-purple-200 rounded-2xl text-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white transition-all uppercase placeholder:normal-case placeholder:text-base placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-300">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 active:scale-98 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-purple-500/25 transition-all duration-200 text-sm tracking-wide mt-3">
                    Acessar Programação
                </button>
            </form>

            <div class="mt-6 text-center border-t border-slate-100 pt-4">
                <a href="/login" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-purple-700 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Voltar para Login de Membros / Líderes
                </a>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="py-6 text-center text-xs font-medium text-slate-400">
        BenApp &copy; <?= date('Y') ?> - Todos os direitos reservados
    </div>

</body>
</html>
