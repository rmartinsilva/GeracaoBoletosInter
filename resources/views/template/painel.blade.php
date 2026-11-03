@inject('dataHora', 'App\Services\DataHoraService')
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Painel Administrativo - Geração de Cobranças' }}</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- CSS Customizado -->
    <link rel="stylesheet" href="{{ url('/assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ url('/assets/css/painel.css') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/assets/images/favicon/favicon-32x32.png') }}">

    <!-- CSRF Token para requisições AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('scripts-topo')

    <style>
        /* Customizações específicas do template */
        .sidebar {
            background: linear-gradient(180deg, #5D4037 0%, #4a332b 100%);
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 2px solid #88B04B;
        }

        .sidebar-menu-item {
            display: block;
            padding: 0.875rem 1.25rem;
            color: #ffffff;
            border-radius: 0.5rem;
            margin: 0.25rem 0.75rem;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            font-weight: 500;
        }

        .sidebar-menu-item:hover {
            background-color: rgba(136, 176, 75, 0.2);
            color: #ffffff;
            transform: translateX(5px);
            text-decoration: none;
        }

        .sidebar-menu-item.active {
            background-color: #88B04B;
            color: #ffffff;
        }

        .sidebar-menu-item i {
            width: 20px;
            display: inline-block;
        }

        .topbar {
            background: var(--painel-bg-primary);
            border-bottom: 3px solid var(--painel-primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .content-area {
            background-color: var(--painel-bg-secondary);
            min-height: calc(100vh - 64px);
        }

        .mobile-menu-btn {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                z-index: 999;
                width: 280px;
                transition: left 0.3s ease;
            }

            .sidebar.mobile-open {
                left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .content-area {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100" data-tema="{{ Auth::user()->getTema() }}">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed left-0 top-0 w-64 z-50">
        <!-- Header do Sidebar -->
        <div class="sidebar-header p-4">
            <img src="{{ url('/assets/images/logo.png') }}" alt="Geração de Cobranças" class="w-full h-auto mb-2" onerror="this.style.display='none'">
            <h2 class="text-white text-center font-bold text-lg">Geração de Cobranças</h2>
        </div>

        <!-- Menu -->
        <nav class="py-4">
            <ul class="space-y-1">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('painel.dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('painel.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>

                @if (isset($listaPermissoes))
                    <!-- Clientes -->
                    @if (in_array('cliente_visualizar', $listaPermissoes))
                        <li>
                            <a href="{{ route('clientes.index') }}" class="sidebar-menu-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Clientes
                            </a>
                        </li>
                    @endif

                    @if (in_array('cliente_editar', $listaPermissoes))
                        <li>
                            <a href="{{ route('cobranca.email.edit') }}" class="sidebar-menu-item {{ request()->routeIs('cobranca.email.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                E-mail da cobrança
                            </a>
                        </li>
                    @endif

                    <!-- Usuários -->
                    @if (in_array('usuario_visualizar', $listaPermissoes))
                        <li>
                            <a href="{{ route('usuarios.index') }}" class="sidebar-menu-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Usuários
                            </a>
                        </li>
                    @endif
                @endif

                <!-- Sair -->
                <li class="mt-4 pt-4 border-t border-white border-opacity-20">
                    <a href="/painel/logout" class="sidebar-menu-item hover:bg-red-600">
                        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sair
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Content -->
    <div class="ml-64 content-area">
        <!-- Top Bar -->
        <header class="topbar px-6 py-4 sticky top-0 z-40">
            <div class="flex items-center justify-between">
                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="mobile-menu-btn text-gray-700 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page Title -->
                <h1 class="text-2xl font-bold painel-text-primary">{{ $titulo ?? 'Dashboard' }}</h1>

                <!-- User Info -->
                <div class="flex items-center space-x-3">
                    <!-- Botão Toggle Tema -->
                    <button id="theme-toggle-btn" class="theme-toggle-btn" title="Alternar tema">
                        <svg id="theme-icon" class="theme-icon-transition" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                        </svg>
                        <svg id="theme-icon-dark" class="theme-icon-transition painel-hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </button>

                    <span style="color: #88B04B;" class="font-semibold">{{ $usuario ?? 'Usuário' }}</span>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        {{ substr($usuario ?? 'U', 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-6">
            @yield('conteudo')
        </main>

        <!-- Footer -->
        <footer class="painel-footer-container px-6 py-4 mt-8">
            <div class="flex flex-col md:flex-row items-center justify-between text-sm">
                <p class="font-medium painel-text-secondary">© {{ $dataHora->agora()->year }} <a href="https://www.usinaweb.com.br" target="_blank">USINAWEB - TECH PRO</a>  - Todos os direitos reservados</p>
                <div class="flex flex-col md:flex-row items-center mt-3 md:mt-0">
                    
                    <div class="w-full mt-2 md:mt-0">
                        <div class="bg-gradient-to-r from-blue-800 via-blue-700 to-blue-900 p-4 shadow-2xl border border-blue-600/30" style="backdrop-filter: blur(10px);">
                            <div class="text-white text-center">
                                <img src="{{ asset('assets/imagens/usinaWeb.png') }}" alt="UsinaWeb" class="h-12 mx-auto filter brightness-110 drop-shadow-lg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ url('/assets/js/app.js') }}"></script>
    <script>
        // Toggle mobile menu
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mobile-open');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !menuBtn.contains(event.target) &&
                sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
            }
        });

        // Enter key navigation
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input:not([type="checkbox"]):not([type="radio"])');
            inputs.forEach((input, index) => {
                input.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        const nextInput = inputs[index + 1];
                        if (nextInput) {
                            nextInput.focus();
                        }
                    }
                });
            });
        });
    </script>

    <!-- Script para controle de tema -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggleBtn = document.getElementById('theme-toggle-btn');
            const themeIcon = document.getElementById('theme-icon');
            const themeIconDark = document.getElementById('theme-icon-dark');
            const body = document.body;

            // Função para aplicar tema
            function aplicarTema(tema) {
                if (tema === 'escuro') {
                    body.classList.add('tema-escuro');
                    themeIcon.classList.add('painel-hidden');
                    themeIconDark.classList.remove('painel-hidden');
                } else {
                    body.classList.remove('tema-escuro');
                    themeIcon.classList.remove('painel-hidden');
                    themeIconDark.classList.add('painel-hidden');
                }
            }

            // Função para fazer toggle do tema
            async function toggleTema() {
                try {
                    const response = await fetch('/painel/configuracao/toggle-tema', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        aplicarTema(data.tema);
                    } else {
                        console.error('Erro ao alterar tema:', data.message);
                    }
                } catch (error) {
                    console.error('Erro na requisição:', error);
                }
            }

            // Event listener para o botão de toggle
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTema);
            }

            // Aplicar tema inicial baseado no data-tema do body
            const temaInicial = body.getAttribute('data-tema');
            if (temaInicial) {
                aplicarTema(temaInicial);
            }
        });
    </script>

    @stack('scripts-rodape')
</body>

</html>
