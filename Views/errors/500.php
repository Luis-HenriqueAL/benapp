<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro de Sistema - BenApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-xl border border-gray-100 text-center">
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Ops! Algo deu errado</h1>
        <p class="text-gray-600 text-sm mb-6">Infelizmente ocorreu um erro inesperado ao processar sua solicitação.</p>
        
        <?php if (isset($errorMessage)): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-left text-xs font-mono text-gray-700 mb-6 overflow-x-auto">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
        <?php endif; ?>

        <a href="/" class="inline-block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-6 rounded-full shadow-lg transition-all active:scale-95 text-sm">
            Voltar ao Início
        </a>
    </div>
</body>
</html>
