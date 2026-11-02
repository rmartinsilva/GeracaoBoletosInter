@extends('template.painel')

@section('conteudo')
    <!-- Cabeçalho -->
    <div class="painel-card rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="mb-4 md:mb-0">
                <h2 class="painel-title text-2xl font-bold">Gerenciar Usuários</h2>
                <p class="painel-text-secondary mt-1">Listagem de todos os usuários do sistema</p>
            </div>
            <a href="{{ route('usuarios.create') }}" class="btn-primary inline-flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Novo Usuário
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if (session('success'))
        <div class="alert-success mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

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

    <!-- Busca -->
    <div class="painel-card rounded-lg shadow-md p-6 mb-6">
        <form action="{{ route('usuarios.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Buscar por nome ou login..."
                       class="painel-input painel-focus-ring">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="painel-btn-primary">
                    <svg class="painel-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
                @if($search)
                    <a href="{{ route('usuarios.index') }}" class="painel-btn-secondary">
                        <svg class="painel-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela de Usuários -->
    <div class="painel-card rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="painel-table">
                <thead class="painel-table-header">
                    <tr>
                        <th class="painel-table-th">Nome</th>
                        <th class="painel-table-th">Login</th>
                        <th class="painel-table-th">Grupos</th>
                        <th class="painel-table-th text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="painel-table-body">
                    @forelse ($usuarios->items() as $usuarioItem)
                        <tr class="painel-table-row">
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3" style="background: linear-gradient(135deg, #88B04B 0%, #6a8c3a 100%);">
                                        {{ substr($usuarioItem->name, 0, 1) }}
                                    </div>
                                    <div class="text-sm font-medium painel-text-primary">{{ $usuarioItem->name }}</div>
                                </div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="text-sm painel-text-primary">{{ $usuarioItem->login }}</div>
                            </td>
                            <td class="painel-table-td">
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($usuarioItem->grupos as $grupo)
                                        <span class="badge badge-primary">{{ $grupo->descricao }}</span>
                                    @empty
                                        <span class="text-sm painel-text-secondary italic">Nenhum grupo</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('usuarios.edit', $usuarioItem->id) }}"
                                       class="painel-btn-action-edit">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                    <form action="{{ route('usuarios.destroy', $usuarioItem->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Tem certeza que deseja deletar este usuário?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="painel-btn-action-delete">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Deletar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="painel-table-td text-center painel-text-secondary">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="mt-2 text-sm">Nenhum usuário encontrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if ($usuarios->total() > 0)
            <div class="painel-pagination">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="text-sm painel-text-secondary mb-4 md:mb-0">
                        Mostrando <span class="font-semibold">{{ $usuarios->from() }}</span>
                        até <span class="font-semibold">{{ $usuarios->to() }}</span>
                        de <span class="font-semibold">{{ $usuarios->total() }}</span> resultados
                    </div>

                    @if ($usuarios->hasPages())
                        <nav class="flex items-center gap-2">
                            @if ($usuarios->onFirstPage())
                                <span class="painel-page-btn painel-page-btn-disabled">
                                    Anterior
                                </span>
                            @else
                                <a href="{{ $usuarios->previousPageUrl() }}" class="painel-page-btn">
                                    Anterior
                                </a>
                            @endif

                            <span class="painel-page-label">
                                Página {{ $usuarios->currentPage() }} de {{ $usuarios->lastPage() }}
                            </span>

                            @if ($usuarios->hasMorePages())
                                <a href="{{ $usuarios->nextPageUrl() }}" class="painel-page-btn">
                                    Próxima
                                </a>
                            @else
                                <span class="painel-page-btn painel-page-btn-disabled">
                                    Próxima
                                </span>
                            @endif
                        </nav>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
