@inject('dataHora', 'App\Services\DataHoraService')
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Geração de Cobranças')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    <div class="mx-auto flex min-h-screen max-w-5xl flex-col px-6">
        <header class="flex items-center justify-between py-6">
            <a href="{{ route('site.index') }}" class="text-sm font-semibold tracking-wide text-stone-900">
                Geração de Cobranças
            </a>
            <a href="{{ route('login') }}" class="rounded-full bg-stone-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-stone-700">
                Painel
            </a>
        </header>

        <main class="flex flex-1 items-center py-16">
            @yield('conteudo')
        </main>

        <footer class="border-t border-stone-200 py-6 text-sm text-stone-500">
            © {{ $dataHora->agora()->year }}
            <a href="https://www.usinaweb.com.br" class="font-medium text-stone-700 hover:text-stone-900" target="_blank" rel="noopener">UsinaWeb Tech Pro</a>
        </footer>
    </div>
</body>
</html>
