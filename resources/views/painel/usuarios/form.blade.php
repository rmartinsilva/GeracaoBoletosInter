@extends('template.painel')

@section('conteudo')
    <!-- Cabeçalho -->
    <div class="painel-card p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold painel-text-primary">
                    {{ $usuarioEdit ? 'Editar Usuário' : 'Novo Usuário' }}
                </h2>
                <p class="painel-text-secondary mt-1">
                    {{ $usuarioEdit ? 'Atualize as informações do usuário' : 'Preencha os dados para criar um novo usuário' }}
                </p>
            </div>
            <a href="{{ route('usuarios.index') }}" class="btn-secondary inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar
            </a>
        </div>
    </div>

    <!-- Alertas de Erro -->
    @if ($errors->any())
        <div class="alert-error mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Formulário -->
    <form action="{{ $usuarioEdit ? route('usuarios.update', $usuarioEdit->id) : route('usuarios.store') }}"
          method="POST"
          class="painel-card p-6">
        @csrf
        @if($usuarioEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nome -->
            <div class="md:col-span-2">
                <label for="name" class="painel-label">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $usuarioEdit->name ?? '') }}"
                           placeholder="Digite o nome completo"
                           required
                           class="painel-input painel-focus-ring pl-10">
                </div>
            </div>

            <!-- Login -->
            <div>
                <label for="login" class="painel-label">
                    Login <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <input type="text"
                           id="login"
                           name="login"
                           value="{{ old('login', $usuarioEdit->login ?? '') }}"
                           placeholder="Digite o login"
                           required
                           class="painel-input painel-focus-ring pl-10">
                </div>
                <p class="mt-1 text-sm painel-text-secondary">Login único para acesso ao sistema</p>
            </div>

            <!-- Senha -->
            <div>
                <label for="password" class="painel-label">
                    Senha @if(!$usuarioEdit)<span class="text-red-500">*</span>@endif
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="{{ $usuarioEdit ? 'Deixe em branco para manter a atual' : 'Digite a senha' }}"
                           @if(!$usuarioEdit) required @endif
                           class="painel-input painel-focus-ring pl-10 pr-10">
                    <button type="button"
                            onclick="togglePasswordField()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <svg id="password-eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="password-eye-slash-icon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @if($usuarioEdit)
                    <p class="mt-1 text-sm painel-text-secondary">Deixe em branco para manter a senha atual</p>
                @else
                    <p class="mt-1 text-sm painel-text-secondary">Mínimo de 6 caracteres</p>
                @endif
            </div>

            <!-- Grupos -->
            <div class="md:col-span-2">
                <label class="painel-label mb-3">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Grupos de Permissões
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 painel-fieldset">
                    @forelse ($grupos as $grupo)
                        <div class="painel-group-item">
                            <input type="checkbox"
                                   id="grupo_{{ $grupo->id }}"
                                   name="grupos[]"
                                   value="{{ $grupo->id }}"
                                   @if($usuarioEdit && $usuarioEdit->grupos->contains($grupo->id)) checked @endif
                                   class="painel-checkbox">
                            <label for="grupo_{{ $grupo->id }}"
                                   class="ml-2 block text-sm cursor-pointer flex-1 painel-text-primary">
                                {{ $grupo->descricao }}
                            </label>
                        </div>
                    @empty
                        <div class="md:col-span-3 text-center painel-text-secondary py-4">
                            Nenhum grupo de permissões cadastrado.
                        </div>
                    @endforelse
                </div>
                <p class="mt-2 text-sm painel-text-secondary">Selecione os grupos de permissões que o usuário terá acesso</p>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-4 mt-8 pt-6 painel-divider-top">
            <a href="{{ route('usuarios.index') }}" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $usuarioEdit ? 'Atualizar Usuário' : 'Criar Usuário' }}
            </button>
        </div>
    </form>

    <script>
        function togglePasswordField() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('password-eye-icon');
            const eyeSlashIcon = document.getElementById('password-eye-slash-icon');

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
@endsection
