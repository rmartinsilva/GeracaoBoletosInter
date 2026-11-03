@extends('template.painel')

@section('conteudo')
    <div class="painel-card p-6 mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold painel-text-primary">
                    {{ $assinaturaEdit ? 'Editar Assinatura' : 'Nova Assinatura' }}
                </h2>
                <p class="painel-text-secondary mt-1">
                    {{ $cliente->nome }}.
                    @if ($assinaturaEdit)
                        O valor novo vale para os próximos lançamentos. Os que já existem permanecem como foram gerados.
                    @else
                        Ao salvar, os lançamentos são gerados do início até o mês atual.
                    @endif
                </p>
            </div>
            <a href="{{ $assinaturaEdit ? route('assinaturas.show', [$cliente->id, $assinaturaEdit->id]) : route('clientes.show', $cliente->id) }}" class="btn-secondary inline-flex items-center">
                Voltar
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-error mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $valorDigitado = old('valor');
        if ($valorDigitado === null && $assinaturaEdit) {
            $valorDigitado = number_format((float) $assinaturaEdit->valor, 2, ',', '.');
        } elseif (is_numeric($valorDigitado)) {
            $valorDigitado = number_format((float) $valorDigitado, 2, ',', '.');
        }
        $periodicidadeAtual = null;
        if ($assinaturaEdit) {
            $periodicidadeAtual = $assinaturaEdit->periodicidade instanceof \App\Enums\Periodicidade
                ? $assinaturaEdit->periodicidade
                : \App\Enums\Periodicidade::from($assinaturaEdit->periodicidade);
        }
        $dataInicioExibida = $assinaturaEdit
            ? \Carbon\CarbonImmutable::parse($assinaturaEdit->data_inicio)->format('d/m/Y')
            : '';
        $dataFimValor = old('data_fim', $assinaturaEdit && $assinaturaEdit->data_fim
            ? \Carbon\CarbonImmutable::parse($assinaturaEdit->data_fim)->toDateString()
            : '');
    @endphp

    <form action="{{ $assinaturaEdit ? route('assinaturas.update', [$cliente->id, $assinaturaEdit->id]) : route('assinaturas.store', $cliente->id) }}"
          method="POST"
          class="painel-card p-6">
        @csrf
        @if ($assinaturaEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="descricao" class="painel-label">
                    Descrição <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="descricao"
                       name="descricao"
                       value="{{ old('descricao', $assinaturaEdit->descricao ?? '') }}"
                       placeholder="Mensalidade do sistema"
                       required
                       maxlength="255"
                       class="painel-input painel-focus-ring">
                <p class="mt-1 text-sm painel-text-secondary">O que está sendo cobrado neste contrato</p>
            </div>

            <div>
                <label for="valor" class="painel-label">
                    Valor <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-sm font-semibold text-gray-400">R$</span>
                    </div>
                    <input type="text"
                           id="valor"
                           name="valor"
                           inputmode="decimal"
                           value="{{ $valorDigitado }}"
                           placeholder="0,00"
                           required
                           data-mascara="dinheiro"
                           class="painel-input painel-focus-ring pl-10">
                </div>
                <p class="mt-1 text-sm painel-text-secondary">Valor de cada cobrança, por exemplo 1.500,00</p>
            </div>

            @if ($assinaturaEdit)
                <div>
                    <span class="painel-label">Periodicidade</span>
                    <p class="painel-text-primary mt-2">{{ $periodicidadeAtual->rotulo() }}</p>
                    <p class="mt-1 text-sm painel-text-secondary">Definida no cadastro, para o histórico não mudar de ritmo</p>
                </div>
                <div>
                    <span class="painel-label">Início</span>
                    <p class="painel-text-primary mt-2">{{ $dataInicioExibida }}</p>
                </div>
            @else
                <div>
                    <label for="periodicidade" class="painel-label">
                        Periodicidade <span class="text-red-500">*</span>
                    </label>
                    @php
                        $periodicidadeSelecionada = old('periodicidade', \App\Enums\Periodicidade::Mensal->value);
                    @endphp
                    <select id="periodicidade"
                            name="periodicidade"
                            required
                            class="painel-input painel-focus-ring">
                        @foreach ($periodicidades as $periodicidade)
                            <option value="{{ $periodicidade->value }}" @selected($periodicidadeSelecionada === $periodicidade->value)>
                                {{ $periodicidade->rotulo() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm painel-text-secondary">Mensal gera uma cobrança por mês. Anual gera uma por ano, no mês de início.</p>
                </div>
                <div>
                    <label for="data_inicio" class="painel-label">
                        Data de início <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           id="data_inicio"
                           name="data_inicio"
                           value="{{ old('data_inicio') }}"
                           required
                           class="painel-input painel-focus-ring">
                </div>
            @endif

            <div>
                <label for="dia_vencimento" class="painel-label">
                    Dia de vencimento <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="dia_vencimento"
                       name="dia_vencimento"
                       min="1"
                       max="31"
                       value="{{ old('dia_vencimento', $assinaturaEdit->dia_vencimento ?? '') }}"
                       class="painel-input painel-focus-ring">
                <p class="mt-1 text-sm painel-text-secondary">Dia do mês de cada cobrança. Em fevereiro, o dia 31 vira o último dia.</p>
            </div>

            <div>
                <label for="dia_geracao" class="painel-label">
                    Dia de geração <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       id="dia_geracao"
                       name="dia_geracao"
                       min="1"
                       max="31"
                       value="{{ old('dia_geracao', $assinaturaEdit->dia_geracao ?? '') }}"
                       class="painel-input painel-focus-ring">
                <p class="mt-1 text-sm painel-text-secondary">Dia em que o boleto é emitido e enviado por e-mail. Se for depois do vencimento, a emissão ocorre no mês anterior.</p>
            </div>

            @if ($assinaturaEdit)
                <div>
                    <label for="situacao" class="painel-label">
                        Situação <span class="text-red-500">*</span>
                    </label>
                    @php
                        $situacaoAtual = $assinaturaEdit->situacao instanceof \App\Enums\SituacaoAssinatura
                            ? $assinaturaEdit->situacao->value
                            : $assinaturaEdit->situacao;
                        $situacaoSelecionada = old('situacao', $situacaoAtual);
                    @endphp
                    <select id="situacao" name="situacao" required class="painel-input painel-focus-ring">
                        @foreach ($situacoes as $situacao)
                            <option value="{{ $situacao->value }}" @selected($situacaoSelecionada === $situacao->value)>
                                {{ $situacao->rotulo() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="data_fim" class="painel-label">
                        Data de encerramento
                    </label>
                    <input type="date"
                           id="data_fim"
                           name="data_fim"
                           value="{{ $dataFimValor }}"
                           class="painel-input painel-focus-ring">
                    <p class="mt-1 text-sm painel-text-secondary">Obrigatória ao encerrar. Lançamentos em aberto com vencimento depois dessa data são cancelados.</p>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-4 mt-8 pt-6 painel-divider-top">
            <a href="{{ $assinaturaEdit ? route('assinaturas.show', [$cliente->id, $assinaturaEdit->id]) : route('clientes.show', $cliente->id) }}" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary inline-flex items-center">
                {{ $assinaturaEdit ? 'Atualizar Assinatura' : 'Cadastrar Assinatura' }}
            </button>
        </div>
    </form>
@endsection
