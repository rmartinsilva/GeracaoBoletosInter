@extends('template.painel')

@section('conteudo')
    <div class="painel-card p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold painel-text-primary">Nova cobrança avulsa</h2>
                <p class="painel-text-secondary mt-1">
                    {{ $cliente->nome }}. O boleto é emitido ao salvar. Não cria assinatura.
                </p>
            </div>
            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn-secondary inline-flex items-center">
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

    <form action="{{ route('cobrancas-avulsas.store', $cliente->id) }}" method="POST" class="painel-card p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="descricao" class="painel-label">
                    Descrição <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="descricao"
                       name="descricao"
                       value="{{ old('descricao') }}"
                       maxlength="255"
                       required
                       class="painel-input painel-focus-ring">
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
                           value="{{ old('valor') }}"
                           placeholder="0,00"
                           required
                           data-mascara="dinheiro"
                           class="painel-input painel-focus-ring pl-10">
                </div>
            </div>

            <div>
                <label for="vencimento" class="painel-label">
                    Vencimento <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       id="vencimento"
                       name="vencimento"
                       value="{{ old('vencimento') }}"
                       required
                       class="painel-input painel-focus-ring">
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 mt-8 pt-6 painel-divider-top">
            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary">Emitir boleto</button>
        </div>
    </form>
@endsection
