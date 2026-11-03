@extends('template.painel')

@inject('dataHora', 'App\Services\DataHoraService')

@section('conteudo')
    @php
        $temPagos = $assinatura->lancamentos->contains(
            fn ($lancamento) => $lancamento->situacao === \App\Enums\SituacaoLancamento::Pago
        );
        $podeGerar = $assinatura->situacao === \App\Enums\SituacaoAssinatura::Ativa
            && ! ($assinatura->periodicidade === \App\Enums\Periodicidade::Avulso && $assinatura->lancamentos->isNotEmpty());
        $hoje = $dataHora->agora()->toDateString();
    @endphp

    <div class="painel-card rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h2 class="painel-title text-2xl font-bold">{{ $assinatura->descricao }}</h2>
                <p class="painel-text-secondary mt-1">
                    <a href="{{ route('clientes.show', $assinatura->cliente_id) }}" class="hover:underline">{{ $assinatura->cliente->nome }}</a>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (in_array('cliente_editar', $listaPermissoes))
                    <a href="{{ route('assinaturas.edit', [$assinatura->cliente_id, $assinatura->id]) }}" class="btn-secondary inline-flex items-center">
                        Editar
                    </a>
                @endif
                <a href="{{ route('clientes.show', $assinatura->cliente_id) }}" class="btn-secondary inline-flex items-center">
                    Voltar
                </a>
            </div>
        </div>

        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div>
                <dt class="text-sm painel-text-secondary">Valor</dt>
                <dd class="font-semibold painel-text-primary">R$ {{ number_format((float) $assinatura->valor, 2, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-sm painel-text-secondary">Periodicidade</dt>
                <dd class="painel-text-primary">{{ $assinatura->periodicidade->rotulo() }}</dd>
            </div>
            <div>
                <dt class="text-sm painel-text-secondary">Vencimento</dt>
                <dd class="painel-text-primary">
                    @if ($assinatura->periodicidade === \App\Enums\Periodicidade::Avulso)
                        {{ $dataHora->formatarData($assinatura->data_inicio) }}
                    @else
                        Dia {{ $assinatura->dia_vencimento }}
                        @if ($assinatura->dia_geracao)
                            <span class="block text-sm painel-text-secondary">Geração no dia {{ $assinatura->dia_geracao }}</span>
                        @endif
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm painel-text-secondary">Situação</dt>
                <dd>
                    <span class="badge {{ $assinatura->situacao === \App\Enums\SituacaoAssinatura::Ativa ? 'badge-success' : 'badge-warning' }}">
                        {{ $assinatura->situacao->rotulo() }}
                    </span>
                    @if ($assinatura->data_fim)
                        <span class="text-sm painel-text-secondary">até {{ $dataHora->formatarData($assinatura->data_fim) }}</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    @if (session('success'))
        <div class="alert-success mb-6">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="painel-card rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold painel-text-primary">Lançamentos</h3>
                <p class="painel-text-secondary mt-1">Cada linha é uma cobrança. O valor fica gravado nela, mesmo que a assinatura mude depois.</p>
            </div>
            @if ($podeGerar && in_array('cliente_editar', $listaPermissoes))
                <form action="{{ route('lancamentos.store', [$assinatura->cliente_id, $assinatura->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="painel-btn-primary">
                        {{ $assinatura->periodicidade === \App\Enums\Periodicidade::Avulso ? 'Gerar lançamento' : 'Gerar próximo lançamento' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="painel-card rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="painel-table">
                <thead class="painel-table-header">
                    <tr>
                        <th class="painel-table-th">Competência</th>
                        <th class="painel-table-th">Vencimento</th>
                        <th class="painel-table-th text-right">Valor</th>
                        <th class="painel-table-th">Situação</th>
                        <th class="painel-table-th">Boleto</th>
                        <th class="painel-table-th">Pago em</th>
                        <th class="painel-table-th">Ações</th>
                    </tr>
                </thead>
                <tbody class="painel-table-body">
                    @forelse ($assinatura->lancamentos as $lancamento)
                        @php
                            $vencido = $lancamento->situacao === \App\Enums\SituacaoLancamento::Aberto
                                && $lancamento->vencimento->toDateString() < $hoje;
                            $rotuloSituacao = $vencido ? 'Vencido' : $lancamento->situacao->rotulo();
                            $classeBadge = match (true) {
                                $vencido => 'badge-danger',
                                $lancamento->situacao === \App\Enums\SituacaoLancamento::Pago => 'badge-success',
                                $lancamento->situacao === \App\Enums\SituacaoLancamento::Cancelado => 'badge-warning',
                                default => 'badge-primary',
                            };
                            $competencia = $assinatura->periodicidade === \App\Enums\Periodicidade::Avulso
                                ? $dataHora->formatarData($lancamento->competencia)
                                : $lancamento->competencia->format('m/Y');
                            $mostrarAntigo = (string) old('lancamento_id') === (string) $lancamento->id;
                        @endphp
                        <tr class="painel-table-row">
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="text-sm painel-text-primary">{{ $competencia }}</div>
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
                                    <div class="text-sm painel-text-secondary">—</div>
                                @endif
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="text-sm painel-text-primary">
                                    {{ $lancamento->pago_em ? $dataHora->formatarData($dataHora->paraExibicao($lancamento->pago_em)) : '—' }}
                                </div>
                            </td>
                            <td class="painel-table-td">
                                @if ($lancamento->situacao === \App\Enums\SituacaoLancamento::Aberto && in_array('cliente_editar', $listaPermissoes))
                                    <div class="flex flex-col gap-3 py-1">
                                        <form action="{{ route('lancamentos.pagamento', [$assinatura->cliente_id, $assinatura->id, $lancamento->id]) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="lancamento_id" value="{{ $lancamento->id }}">
                                            <input type="date"
                                                   name="data_pagamento"
                                                   aria-label="Data do pagamento"
                                                   value="{{ $mostrarAntigo ? old('data_pagamento') : $hoje }}"
                                                   class="painel-input painel-focus-ring max-w-[11rem]">
                                            <button type="submit" class="painel-btn-primary">Registrar pagamento</button>
                                        </form>
                                        <details>
                                            <summary class="text-sm painel-text-secondary cursor-pointer">Ajustar</summary>
                                            <form action="{{ route('lancamentos.update', [$assinatura->cliente_id, $assinatura->id, $lancamento->id]) }}" method="POST" class="flex flex-wrap items-center gap-2 mt-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="lancamento_id" value="{{ $lancamento->id }}">
                                                <input type="text"
                                                       name="valor"
                                                       aria-label="Valor do lançamento"
                                                       inputmode="decimal"
                                                       value="{{ $mostrarAntigo && old('valor') !== null ? old('valor') : number_format((float) $lancamento->valor, 2, ',', '.') }}"
                                                       class="painel-input painel-focus-ring max-w-[8rem]">
                                                <input type="date"
                                                       name="vencimento"
                                                       aria-label="Vencimento"
                                                       value="{{ $mostrarAntigo && old('vencimento') ? old('vencimento') : $lancamento->vencimento->toDateString() }}"
                                                       class="painel-input painel-focus-ring max-w-[11rem]">
                                                <button type="submit" class="painel-btn-secondary">Salvar</button>
                                            </form>
                                        </details>
                                        <form action="{{ route('lancamentos.cancelar', [$assinatura->cliente_id, $assinatura->id, $lancamento->id]) }}" method="POST" onsubmit="return confirm('Cancelar este lançamento? Ele continua no histórico.');">
                                            @csrf
                                            <button type="submit" class="painel-btn-action-delete">Cancelar</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="painel-table-td text-center painel-text-secondary">
                                <p class="text-sm">Nenhum lançamento gerado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (in_array('cliente_deletar', $listaPermissoes))
        <div class="mt-6">
            @if ($temPagos)
                <p class="text-sm painel-text-secondary">Há pagamentos registrados. Encerre a assinatura para manter o histórico.</p>
            @else
                <form action="{{ route('assinaturas.destroy', [$assinatura->cliente_id, $assinatura->id]) }}" method="POST" onsubmit="return confirm('Excluir esta assinatura e os lançamentos que ainda não foram pagos?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="painel-btn-action-delete">Excluir assinatura</button>
                </form>
            @endif
        </div>
    @endif
@endsection
