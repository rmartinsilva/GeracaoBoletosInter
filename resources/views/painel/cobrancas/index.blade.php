@extends('template.painel')

@inject('dataHora', 'App\Services\DataHoraService')

@section('conteudo')
    @php
        $parametros = [
            'de' => $filtro->de,
            'ate' => $filtro->ate,
            'busca' => $filtro->busca,
        ];
        $atalho = function (?string $situacao = null, ?string $emissao = null) use ($parametros): string {
            return route('cobrancas.index', array_filter([
                ...$parametros,
                'situacao' => $situacao,
                'emissao' => $emissao,
            ], fn ($valor) => $valor !== null && $valor !== ''));
        };
        $situacaoAtiva = $filtro->situacao?->value;
        $emissaoAtiva = $filtro->emissao?->value;
        $cartaoAtivo = fn (?string $situacao, ?string $emissao): string => $situacaoAtiva === $situacao && $emissaoAtiva === $emissao
            ? 'ring-2 ring-[#88B04B]'
            : '';
        $emitidas = $resumo->emitidas === 1 ? '1 emitida' : $resumo->emitidas.' emitidas';
        $canceladas = $resumo->canceladas === 1 ? '1 cancelada' : $resumo->canceladas.' canceladas';
    @endphp

    <div class="painel-card rounded-lg shadow-md p-6 mb-6">
        <h2 class="painel-title text-2xl font-bold">Cobranças do período</h2>
        <p class="painel-text-secondary mt-1">
            Vencimentos de {{ $dataHora->formatarData(\Carbon\CarbonImmutable::parse($filtro->de)) }}
            a {{ $dataHora->formatarData(\Carbon\CarbonImmutable::parse($filtro->ate)) }},
            de assinatura e avulsas. A baixa é a data em que o pagamento foi registrado.
        </p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <a href="{{ $atalho() }}" class="painel-card rounded-lg shadow-md p-4 block {{ $cartaoAtivo(null, null) }}">
            <p class="text-sm painel-text-secondary">No período</p>
            <p class="text-2xl font-bold painel-text-primary">{{ $resumo->quantidade }}</p>
            <p class="text-sm painel-text-secondary">R$ {{ number_format((float) $resumo->valor, 2, ',', '.') }}</p>
            <p class="text-sm painel-text-secondary">{{ $emitidas }} · {{ $canceladas }}</p>
        </a>
        <a href="{{ $atalho('pago') }}" class="painel-card rounded-lg shadow-md p-4 block {{ $cartaoAtivo('pago', null) }}">
            <p class="text-sm painel-text-secondary">Baixadas</p>
            <p class="text-2xl font-bold painel-text-primary">{{ $resumo->pagas }}</p>
            <p class="text-sm painel-text-secondary">R$ {{ number_format((float) $resumo->valor_pago, 2, ',', '.') }}</p>
        </a>
        <a href="{{ $atalho('aberto') }}" class="painel-card rounded-lg shadow-md p-4 block {{ $cartaoAtivo('aberto', null) }}">
            <p class="text-sm painel-text-secondary">Em aberto</p>
            <p class="text-2xl font-bold painel-text-primary">{{ $resumo->abertas }}</p>
            <p class="text-sm painel-text-secondary">R$ {{ number_format((float) $resumo->valor_aberto, 2, ',', '.') }}</p>
        </a>
        <a href="{{ $atalho('vencido') }}" class="painel-card rounded-lg shadow-md p-4 block {{ $cartaoAtivo('vencido', null) }}">
            <p class="text-sm painel-text-secondary">Vencidas</p>
            <p class="text-2xl font-bold painel-text-primary">{{ $resumo->vencidas }}</p>
        </a>
        <a href="{{ $atalho(null, 'sem_boleto') }}" class="painel-card rounded-lg shadow-md p-4 block {{ $cartaoAtivo(null, 'sem_boleto') }}">
            <p class="text-sm painel-text-secondary">Sem boleto</p>
            <p class="text-2xl font-bold painel-text-primary">{{ $resumo->sem_boleto }}</p>
        </a>
        <a href="{{ $atalho(null, 'falha') }}" class="painel-card rounded-lg shadow-md p-4 block {{ $cartaoAtivo(null, 'falha') }}">
            <p class="text-sm painel-text-secondary">Com falha</p>
            <p class="text-2xl font-bold painel-text-primary">{{ $resumo->falhas }}</p>
        </a>
    </div>

    <div class="painel-card rounded-lg shadow-md p-6 mb-6">
        <form action="{{ route('cobrancas.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4 items-end">
            <div>
                <label for="de" class="block text-sm painel-text-secondary mb-1">Vencimento de</label>
                <input type="date" id="de" name="de" value="{{ $filtro->de }}" class="painel-input painel-focus-ring">
            </div>
            <div>
                <label for="ate" class="block text-sm painel-text-secondary mb-1">até</label>
                <input type="date" id="ate" name="ate" value="{{ $filtro->ate }}" class="painel-input painel-focus-ring">
            </div>
            <div class="xl:col-span-2">
                <label for="busca" class="block text-sm painel-text-secondary mb-1">Cliente ou descrição</label>
                <input type="text" id="busca" name="busca" value="{{ $filtro->busca }}" placeholder="Nome do cliente ou descrição" class="painel-input painel-focus-ring">
            </div>
            <div>
                <label for="situacao" class="block text-sm painel-text-secondary mb-1">Situação</label>
                <select id="situacao" name="situacao" class="painel-input painel-focus-ring">
                    <option value="" @selected($situacaoAtiva === null)>Todas</option>
                    @foreach (\App\Enums\FiltroSituacaoCobranca::cases() as $opcao)
                        <option value="{{ $opcao->value }}" @selected($situacaoAtiva === $opcao->value)>{{ $opcao->rotulo() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="emissao" class="block text-sm painel-text-secondary mb-1">Boleto</label>
                <select id="emissao" name="emissao" class="painel-input painel-focus-ring">
                    <option value="" @selected($emissaoAtiva === null)>Todos</option>
                    @foreach (\App\Enums\FiltroEmissaoCobranca::cases() as $opcao)
                        <option value="{{ $opcao->value }}" @selected($emissaoAtiva === $opcao->value)>{{ $opcao->rotulo() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 md:col-span-2 xl:col-span-6">
                <button type="submit" class="painel-btn-primary">Filtrar</button>
                @if (request()->except('page'))
                    <a href="{{ route('cobrancas.index') }}" class="painel-btn-secondary">Limpar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="painel-card rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="painel-table">
                <thead class="painel-table-header">
                    <tr>
                        <th class="painel-table-th">Cliente</th>
                        <th class="painel-table-th">Cobrança</th>
                        <th class="painel-table-th">Vencimento</th>
                        <th class="painel-table-th text-right">Valor</th>
                        <th class="painel-table-th">Situação</th>
                        <th class="painel-table-th">Boleto</th>
                        <th class="painel-table-th">Baixa</th>
                        <th class="painel-table-th text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="painel-table-body">
                    @forelse ($cobrancas->items() as $lancamento)
                        @php
                            $cliente = $lancamento->assinatura?->cliente ?? $lancamento->cliente;
                            $descricao = $lancamento->assinatura?->descricao ?? $lancamento->descricao;
                            $origem = $lancamento->assinatura_id ? 'Assinatura' : 'Avulsa';
                            $vencido = $lancamento->situacao === \App\Enums\SituacaoLancamento::Aberto
                                && $lancamento->vencimento->toDateString() < $filtro->hoje;
                            $rotuloSituacao = $vencido ? 'Vencido' : $lancamento->situacao->rotulo();
                            $classeBadge = match (true) {
                                $vencido => 'badge-danger',
                                $lancamento->situacao === \App\Enums\SituacaoLancamento::Pago => 'badge-success',
                                $lancamento->situacao === \App\Enums\SituacaoLancamento::Cancelado => 'badge-warning',
                                default => 'badge-primary',
                            };
                            $destino = $lancamento->assinatura_id
                                ? route('assinaturas.show', [$lancamento->assinatura->cliente_id, $lancamento->assinatura_id])
                                : route('clientes.show', $lancamento->cliente_id);
                        @endphp
                        <tr class="painel-table-row">
                            <td class="painel-table-td">
                                @if ($cliente)
                                    <a href="{{ route('clientes.show', $cliente->id) }}" class="text-sm font-medium painel-text-primary hover:underline">{{ $cliente->nome }}</a>
                                @endif
                            </td>
                            <td class="painel-table-td">
                                <div class="text-sm painel-text-primary">{{ $descricao }}</div>
                                <div class="text-sm painel-text-secondary">{{ $origem }} · {{ $lancamento->competencia->format('m/Y') }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="text-sm painel-text-primary">{{ $dataHora->formatarData($lancamento->vencimento) }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap text-right">
                                <div class="text-sm font-semibold painel-text-primary">R$ {{ number_format((float) $lancamento->valor, 2, ',', '.') }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <span class="badge {{ $classeBadge }}">{{ $rotuloSituacao }}</span>
                            </td>
                            <td class="painel-table-td">
                                @if ($lancamento->email_enviado_em)
                                    <div class="text-sm painel-text-primary">E-mail enviado</div>
                                @elseif ($lancamento->erro_emissao)
                                    <div class="text-sm text-red-600">{{ $lancamento->erro_emissao }}</div>
                                @elseif ($lancamento->codigo_solicitacao)
                                    <div class="text-sm painel-text-secondary">Aguardando boleto</div>
                                @else
                                    <div class="text-sm painel-text-secondary">Sem boleto</div>
                                @endif
                                @if ($lancamento->nosso_numero)
                                    <div class="text-sm painel-text-secondary">Nosso número {{ $lancamento->nosso_numero }}</div>
                                @endif
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="text-sm painel-text-primary">
                                    {{ $lancamento->pago_em ? $dataHora->formatarDataHora($lancamento->pago_em) : '—' }}
                                </div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap text-center">
                                <a href="{{ $destino }}" class="painel-btn-action-edit">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="painel-table-td text-center painel-text-secondary">
                                <p class="text-sm">Nenhuma cobrança neste período.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($cobrancas->total() > 0)
            <div class="painel-pagination">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="text-sm painel-text-secondary mb-4 md:mb-0">
                        Mostrando <span class="font-semibold">{{ $cobrancas->from() }}</span>
                        até <span class="font-semibold">{{ $cobrancas->to() }}</span>
                        de <span class="font-semibold">{{ $cobrancas->total() }}</span> resultados
                    </div>

                    @if ($cobrancas->hasPages())
                        <nav class="flex items-center gap-2">
                            @if ($cobrancas->isFirstPage())
                                <span class="painel-page-btn painel-page-btn-disabled">Anterior</span>
                            @else
                                <a href="{{ request()->fullUrlWithQuery(['page' => $cobrancas->getNumberPreviousPage()]) }}" class="painel-page-btn">Anterior</a>
                            @endif

                            <span class="painel-page-label">
                                Página {{ $cobrancas->currentPage() }} de {{ $cobrancas->totalPages() }}
                            </span>

                            @if ($cobrancas->isLastPage())
                                <span class="painel-page-btn painel-page-btn-disabled">Próxima</span>
                            @else
                                <a href="{{ request()->fullUrlWithQuery(['page' => $cobrancas->getNumberNextPage()]) }}" class="painel-page-btn">Próxima</a>
                            @endif
                        </nav>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
