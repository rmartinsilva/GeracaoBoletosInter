@extends('template.painel')

@section('conteudo')
    <!-- Cabeçalho do Dashboard -->
    <div class="painel-card p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold painel-text-primary">Bem-vindo, {{ $usuario }}!</h2>
                <p class="painel-text-secondary mt-2">Gerencie seu painel administrativo</p>
            </div>
            <div class="hidden md:block">
                <div class="flex items-center space-x-2 px-4 py-2 rounded-lg painel-info-chip">
                    <svg class="w-6 h-6 text-painel-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-semibold painel-text-primary">{{ date('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Cards de Acesso Rápido -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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

        @if (in_array('bannerPrincipal_visualizar', $listaPermissoes))
            <a href="/painel/bannerPrincipal" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Banner Principal</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar banners do site</p>
                </div>
            </a>
        @endif

        @if (in_array('empresa_visualizar', $listaPermissoes))
            <a href="/painel/empresa" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Empresa</h3>
                    <p class="painel-text-secondary text-sm text-center">Informações da empresa</p>
                </div>
            </a>
        @endif

        @if (in_array('clientes_visualizar', $listaPermissoes))
            <a href="/painel/clientes" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Clientes</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar clientes</p>
                </div>
            </a>
        @endif

        @if (in_array('servicos_visualizar', $listaPermissoes))
            <a href="/painel/servicos" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Serviços</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar serviços</p>
                </div>
            </a>
        @endif

        @if (in_array('projetos_visualizar', $listaPermissoes))
            <a href="/painel/projetos" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Portfólio</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar projetos</p>
                </div>
            </a>
        @endif

        @if (in_array('lancamentos_visualizar', $listaPermissoes))
            <a href="/painel/lancamentos" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Lançamentos</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar lançamentos</p>
                </div>
            </a>
        @endif

        @if (in_array('equipe_visualizar', $listaPermissoes))
            <a href="/painel/equipe" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Equipe</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar equipe</p>
                </div>
            </a>
        @endif

        @if (in_array('depoimentos_visualizar', $listaPermissoes))
            <a href="/painel/depoimento" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Depoimentos</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar depoimentos</p>
                </div>
            </a>
        @endif

        @if (in_array('noticias_visualizar', $listaPermissoes))
            <a href="/painel/noticia" class="dashboard-card group">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 transition-all group-hover:scale-110" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 painel-text-primary">Notícias</h3>
                    <p class="painel-text-secondary text-sm text-center">Gerenciar notícias</p>
                </div>
            </a>
        @endif
    </div>

    
@endsection
