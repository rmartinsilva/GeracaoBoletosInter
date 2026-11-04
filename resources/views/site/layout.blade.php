@inject('dataHora', 'App\Services\DataHoraService')
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Geração de Cobranças')</title>
    <link rel="stylesheet" href="{{ url('/assets/css/site.css') }}">
</head>
<body>
    <div class="site">
        <header class="site-header">
            <a href="{{ route('site.index') }}" class="site-marca">
                Geração de Cobranças
            </a>
            <a href="{{ route('login') }}" class="site-botao">
                Painel
            </a>
        </header>

        <main class="site-main">
            @yield('conteudo')
        </main>

        <footer class="site-footer">
            © {{ $dataHora->agora()->year }}
            <a href="https://www.usinaweb.com.br" target="_blank" rel="noopener">UsinaWeb Tech Pro</a>
        </footer>
    </div>
</body>
</html>
