@inject('dataHora', 'App\Services\DataHoraService')
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- CSS Customizado -->
    <link rel="stylesheet" href="{{ url('/assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ url('/assets/css/painel.css') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/assets/images/favicon/favicon-32x32.png') }}">
</head>

<body class="painel-gradient-bg">
    <div class="painel-container">
        <div class="painel-login-card animate-fadeIn">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="usinaweb-container mb-4">
                    <div class="usinaweb-logo-wrapper">
                        <img src="{{ asset('assets/imagens/usinaWeb.png') }}" alt="UsinaWeb" class="usinaweb-logo">
                    </div>
                </div>
                <h1 class="painel-title">Geração de Cobranças</h1>
            </div>

            <!-- Alertas de Erro -->
            @if ($errors->any())
                <div class="alert-error mb-6" data-auto-close="true">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Formulário de Login -->
            <form action="{{ route('loginpainel') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Campo Login -->
                <div>
                    <label for="txtEmail" class="painel-label">
                        Login
                    </label>
                    <div class="painel-input-container">
                        <svg class="painel-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <input type="text" id="txtEmail" name="txtEmail" value="{{ old('txtEmail') }}"
                            placeholder="Digite seu login" required autofocus class="painel-input painel-focus-ring">
                    </div>
                </div>

                <!-- Campo Senha -->
                <div>
                    <label for="txtSenha" class="painel-label">
                        Senha
                    </label>
                    <div class="painel-input-container">
                        <svg class="painel-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <input type="password" id="txtSenha" name="txtSenha" placeholder="Digite sua senha" required
                            class="painel-input painel-focus-ring pr-10">
                        <!-- Botão Toggle Senha -->
                        <button type="button" onclick="togglePassword()"
                            class="painel-password-toggle">
                            <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-slash-icon" class="h-5 w-5 painel-hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Lembrar-me -->
                <div class="flex items-center">
                    <input type="checkbox" id="relembrarsenha" name="relembrarsenha" value="Sim"
                        class="painel-checkbox">
                    <label for="relembrarsenha" class="painel-checkbox-label">
                        Lembrar-me neste dispositivo
                    </label>
                </div>

                <div class="login-actions">
                    <a href="{{ route('site.index') }}" class="login-btn login-btn-back">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Voltar
                    </a>
                    <button type="submit" class="login-btn login-btn-enter">
                        Entrar
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="painel-footer">
                <div class="painel-footer-text">
                    © 2008 - {{ $dataHora->agora()->year }} <a href="https://www.usinaweb.com.br" target="_blank">USINAWEB - TECH PRO</a> - Todos os direitos reservados
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle de visibilidade da senha
        function togglePassword() {
            const passwordInput = document.getElementById('txtSenha');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashIcon = document.getElementById('eye-slash-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>
    <script src="{{ url('/assets/js/app.js') }}"></script>
</body>

</html>

