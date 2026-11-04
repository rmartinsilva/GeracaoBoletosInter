@extends('template.painel')
@inject('dataHora', 'App\Services\DataHoraService')

@section('conteudo')
    <!-- Cabeçalho do Dashboard -->
    <div class="painel-card p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold painel-text-primary">Bem-vindo, {{ $usuario }}!</h2>
                <p class="painel-text-secondary mt-2">Gerencie os clientes e os usuários do sistema</p>
            </div>
            <div class="hidden md:block">
                <div class="flex items-center space-x-2 px-4 py-2 rounded-lg painel-info-chip">
                    <svg class="w-6 h-6 text-painel-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-semibold painel-text-primary">{{ $dataHora->formatarData($dataHora->agora()) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success mb-6">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error mb-6">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    @if (in_array('cliente_editar', $listaPermissoes))
        <div class="painel-card p-6 mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-bold painel-text-primary">Cobranças do dia</h3>
                    <p class="painel-text-secondary mt-1">A rotina automática roda todo dia às 08:00 e coloca as cobranças na fila. O banco recebe uma a cada minuto. Gere agora se o banco ficou indisponível ou se alguma cobrança ficou sem boleto.</p>
                </div>
                <form method="POST" action="{{ route('cobrancas.gerar') }}" onsubmit="if (!confirm('Colocar as cobranças do dia na fila? O banco recebe uma a cada minuto, e as que ficaram sem e-mail entram de novo.')) { return false; } this.querySelector('button').disabled = true;">
                    @csrf
                    <button type="submit" class="btn-primary inline-flex items-center justify-center whitespace-nowrap">Gerar cobranças agora</button>
                </form>
            </div>
        </div>
    @endif

    <!-- Grid de Cards de Acesso Rápido -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @if (in_array('cliente_visualizar', $listaPermissoes))
            <a href="{{ route('clientes.index') }}" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Clientes</h3>
                    <p class="painel-text-secondary text-sm text-center">Cadastro de clientes e suas assinaturas</p>
                </div>
            </a>
        @endif

        @if (in_array('usuario_visualizar', $listaPermissoes))
            <a href="/painel/usuarios" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Usuários</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar usuários do sistema</p>
                </div>
            </a>
        @endif
    </div>

    
@endsection
