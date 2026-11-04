@extends('template.painel')

@inject('dataHora', 'App\Services\DataHoraService')

@section('conteudo')
    @php
        $situacaoCliente = \App\Enums\SituacaoCliente::tryFrom((string) ($cliente->situacao ?? 'ativo')) ?? \App\Enums\SituacaoCliente::Ativo;
        $clienteAtivo = $situacaoCliente === \App\Enums\SituacaoCliente::Ativo;
    @endphp
    <div class="painel-card rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="painel-title text-2xl font-bold">{{ $cliente->nome }}</h2>
                    <span class="badge {{ $clienteAtivo ? 'badge-success' : 'badge-warning' }}">{{ $situacaoCliente->rotulo() }}</span>
                </div>
                <p class="painel-text-secondary mt-1">{{ $cliente->email }}</p>
                @if (! empty($cliente->emails_adicionais))
                    <p class="painel-text-secondary whitespace-pre-line">{{ $cliente->emails_adicionais }}</p>
                @endif
                <p class="painel-text-secondary">Contato: {{ $cliente->nome_contato }}</p>
                @if ($cliente->cidade)
                    <p class="painel-text-secondary">{{ $cliente->endereco }}{{ $cliente->numero ? ', '.$cliente->numero : '' }} — {{ $cliente->cidade }}/{{ $cliente->uf }}</p>
                @endif
                @unless ($clienteAtivo)
                    <p class="painel-text-secondary mt-2">Cliente inativo. As assinaturas foram interrompidas e não há cobrança nova.</p>
                @endunless
            </div>
            <div class="flex flex-wrap gap-2">
                @if (in_array('cliente_editar', $listaPermissoes))
                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn-secondary inline-flex items-center">
                        Editar
                    </a>
                    @if ($clienteAtivo)
                        <form action="{{ route('clientes.inativar', $cliente->id) }}" method="POST" class="inline-flex" onsubmit="return confirm('Inativar este cliente encerra as assinaturas ativas e cancela os lançamentos com vencimento posterior. O histórico permanece. Continuar?');">
                            @csrf
                            <button type="submit" class="btn-danger inline-flex items-center">Inativar</button>
                        </form>
                    @else
                        <form action="{{ route('clientes.reativar', $cliente->id) }}" method="POST" class="inline-flex" onsubmit="return confirm('Reativar o cliente não reabre as assinaturas encerradas. Continuar?');">
                            @csrf
                            <button type="submit" class="btn-success inline-flex items-center">Reativar</button>
                        </form>
                    @endif
                @endif
                <a href="{{ route('clientes.index') }}" class="btn-secondary inline-flex items-center">
                    Voltar
                </a>
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
                <h3 class="text-lg font-bold painel-text-primary">Assinaturas</h3>
                <p class="painel-text-secondary mt-1">Cada assinatura gera os lançamentos do período e guarda o histórico.</p>
            </div>
            @if ($clienteAtivo && in_array('cliente_cadastrar', $listaPermissoes))
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('cobrancas-avulsas.create', $cliente->id) }}" class="btn-secondary inline-flex items-center justify-center">
                        Cobrança avulsa
                    </a>
                    <a href="{{ route('assinaturas.create', $cliente->id) }}" class="btn-primary inline-flex items-center justify-center">
                        Nova Assinatura
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="painel-card rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="painel-table">
                <thead class="painel-table-header">
                    <tr>
                        <th class="painel-table-th">Descrição</th>
                        <th class="painel-table-th text-right">Valor</th>
                        <th class="painel-table-th">Periodicidade</th>
                        <th class="painel-table-th">Início</th>
                        <th class="painel-table-th">Lançamentos</th>
                        <th class="painel-table-th">Situação</th>
                        <th class="painel-table-th text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="painel-table-body">
                    @forelse ($assinaturas as $assinatura)
                        @php
                            $quantidade = (int) $assinatura->lancamentos_count;
                            $rotuloLancamentos = $quantidade === 1 ? '1 lançamento' : $quantidade.' lançamentos';
                            $classeSituacao = $assinatura->situacao === \App\Enums\SituacaoAssinatura::Ativa ? 'badge-success' : 'badge-warning';
                        @endphp
                        <tr class="painel-table-row">
                            <td class="painel-table-td">
                                <div class="text-sm font-medium painel-text-primary">{{ $assinatura->descricao }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap text-right">
                                <div class="text-sm font-semibold painel-text-primary">R$ {{ number_format((float) $assinatura->valor, 2, ',', '.') }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <span class="badge badge-primary">{{ $assinatura->periodicidade->rotulo() }}</span>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="text-sm painel-text-primary">{{ $dataHora->formatarData($assinatura->data_inicio) }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="text-sm painel-text-primary">{{ $rotuloLancamentos }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <span class="badge {{ $classeSituacao }}">{{ $assinatura->situacao->rotulo() }}</span>
                            </td>
                            <td class="painel-table-td whitespace-nowrap text-center">
                                <a href="{{ route('assinaturas.show', [$cliente->id, $assinatura->id]) }}" class="painel-btn-action-edit">
                                    Lançamentos
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="painel-table-td text-center painel-text-secondary">
                                <p class="text-sm">Nenhuma assinatura cadastrada.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="painel-card rounded-lg shadow-md p-6 mt-6 mb-6">
        <h3 class="text-lg font-bold painel-text-primary">Cobranças avulsas</h3>
        <p class="painel-text-secondary mt-1">Cada linha é um boleto emitido na hora, sem assinatura.</p>
    </div>

    <div class="painel-card rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="painel-table">
                <thead class="painel-table-header">
                    <tr>
                        <th class="painel-table-th">Descrição</th>
                        <th class="painel-table-th text-right">Valor</th>
                        <th class="painel-table-th">Vencimento</th>
                        <th class="painel-table-th">Situação</th>
                        <th class="painel-table-th">Boleto</th>
                    </tr>
                </thead>
                <tbody class="painel-table-body">
                    @forelse ($cobrancasAvulsas as $cobranca)
                        @php
                            $vencido = $cobranca->situacao === \App\Enums\SituacaoLancamento::Aberto
                                && $cobranca->vencimento->toDateString() < $dataHora->agora()->toDateString();
                            $rotuloSituacao = $vencido ? 'Vencido' : $cobranca->situacao->rotulo();
                        @endphp
                        <tr class="painel-table-row">
                            <td class="painel-table-td">
                                <div class="text-sm font-medium painel-text-primary">{{ $cobranca->descricao }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap text-right">
                                <div class="text-sm font-semibold painel-text-primary">R$ {{ number_format((float) $cobranca->valor, 2, ',', '.') }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <div class="text-sm painel-text-primary">{{ $dataHora->formatarData($cobranca->vencimento) }}</div>
                            </td>
                            <td class="painel-table-td whitespace-nowrap">
                                <span class="badge badge-primary">{{ $rotuloSituacao }}</span>
                            </td>
                            <td class="painel-table-td">
                                @if ($cobranca->email_enviado_em)
                                    <div class="text-sm painel-text-primary">E-mail enviado</div>
                                @elseif ($cobranca->erro_emissao)
                                    <div class="text-sm text-red-600">{{ $cobranca->erro_emissao }}</div>
                                @elseif ($cobranca->codigo_solicitacao)
                                    <div class="text-sm painel-text-secondary">Aguardando boleto</div>
                                @else
                                    <div class="text-sm painel-text-secondary">—</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="painel-table-td text-center painel-text-secondary">
                                <p class="text-sm">Nenhuma cobrança avulsa.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
