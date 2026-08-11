<?php 
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - BenApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col justify-between antialiased">

    <!-- Top Wave Header inspired by design_modelo.jpg -->
    <div class="relative bg-gradient-to-b from-blue-700 via-blue-600 to-indigo-700 text-white pt-12 pb-24 px-6 rounded-b-[40px] shadow-xl text-center overflow-hidden">
        <!-- Decorative SVG background shapes -->
        <div class="absolute top-0 left-0 w-full h-full opacity-15 pointer-events-none">
            <svg class="w-full h-full" viewBox="0 0 400 400" fill="none">
                <circle cx="50" cy="50" r="100" fill="white" />
                <circle cx="350" cy="150" r="120" fill="white" />
            </svg>
        </div>

        <!-- Logo Icon -->
        <div class="relative z-10 mx-auto w-24 h-24 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center p-3 border border-white/30 shadow-inner mb-4">
            <div class="w-16 h-16 bg-white text-blue-600 rounded-full flex items-center justify-center font-black text-2xl shadow-md">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>

        <h1 class="relative z-10 text-3xl font-extrabold tracking-tight">Gestão de Células</h1>
        <p class="relative z-10 text-blue-100 text-sm mt-1">BenApp - Entre para acessar suas escalas</p>
    </div>

    <!-- Login Card Form -->
    <div class="flex-1 px-6 -mt-16 relative z-20 max-w-md mx-auto w-full">
        <div class="bg-white rounded-3xl shadow-xl p-6 sm:p-8 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 text-center mb-6">Acesse sua Conta</h2>

            <?php if ($error): ?>
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg text-red-700 text-sm flex items-center justify-between">
                    <span><?= htmlspecialchars($error) ?></span>
                    <button onclick="this.parentElement.remove()" class="text-red-500 font-bold ml-2">&times;</button>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= \Helpers\SecurityHelper::generateCsrfToken() ?>">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">E-mail</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </span>
                        <input type="email" name="email" value="admin@celula.com" required placeholder="seu@email.com" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Senha</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input type="password" name="password" value="senha123" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3.5 px-6 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 active:scale-95 text-base tracking-wide mt-4">
                    Entrar no Sistema
                </button>
            </form>

            <div class="mt-6 text-center border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-400">Credenciais de teste:</p>
                <p class="text-xs font-semibold text-blue-600 mt-1">E-mail: admin@celula.com | Senha: senha123</p>
            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="py-6 text-center text-xs text-gray-400">
        BenApp &copy; <?= date('Y') ?> - Todos os direitos reservados
    </div>

</body>
</html>
