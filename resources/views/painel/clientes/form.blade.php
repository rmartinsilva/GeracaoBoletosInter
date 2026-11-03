@extends('template.painel')

@section('conteudo')
    <!-- Cabeçalho -->
    <div class="painel-card p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold painel-text-primary">
                    {{ $clienteEdit ? 'Editar Cliente' : 'Novo Cliente' }}
                </h2>
                <p class="painel-text-secondary mt-1">
                    {{ $clienteEdit ? 'Atualize as informações do cliente.' : 'Preencha os dados para cadastrar um novo cliente.' }}
                    O valor e a periodicidade ficam na assinatura.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($clienteEdit)
                    @php
                        $situacaoCliente = \App\Enums\SituacaoCliente::tryFrom((string) ($clienteEdit->situacao ?? 'ativo')) ?? \App\Enums\SituacaoCliente::Ativo;
                    @endphp
                    <a href="{{ route('clientes.show', $clienteEdit->id) }}" class="btn-primary inline-flex items-center">
                        Assinaturas
                    </a>
                    @if ($situacaoCliente === \App\Enums\SituacaoCliente::Ativo)
                        <form action="{{ route('clientes.inativar', $clienteEdit->id) }}" method="POST" class="inline-flex" onsubmit="return confirm('Inativar este cliente encerra as assinaturas ativas e cancela os lançamentos com vencimento posterior. O histórico permanece. Continuar?');">
                            @csrf
                            <button type="submit" class="btn-danger inline-flex items-center">Inativar</button>
                        </form>
                    @else
                        <form action="{{ route('clientes.reativar', $clienteEdit->id) }}" method="POST" class="inline-flex" onsubmit="return confirm('Reativar o cliente não reabre as assinaturas encerradas. Continuar?');">
                            @csrf
                            <button type="submit" class="btn-success inline-flex items-center">Reativar</button>
                        </form>
                    @endif
                @endif
                <a href="{{ route('clientes.index') }}" class="btn-secondary inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Voltar
                </a>
            </div>
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
    <form action="{{ $clienteEdit ? route('clientes.update', $clienteEdit->id) : route('clientes.store') }}"
          method="POST"
          class="painel-card p-6">
        @csrf
        @if($clienteEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nome do cliente -->
            <div class="md:col-span-2">
                <label for="nome" class="painel-label">
                    Nome do Cliente <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <input type="text"
                           id="nome"
                           name="nome"
                           value="{{ old('nome', $clienteEdit->nome ?? '') }}"
                           placeholder="Razão social ou nome do cliente"
                           required
                           maxlength="255"
                           class="painel-input painel-focus-ring pl-10">
                </div>
            </div>

            <!-- E-mail -->
            <div>
                <label for="email" class="painel-label">
                    E-mail <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $clienteEdit->email ?? '') }}"
                           placeholder="financeiro@cliente.com.br"
                           required
                           maxlength="255"
                           autocomplete="email"
                           inputmode="email"
                           pattern="[^@\s]+@[^@\s]+\.[^@\s]+"
                           title="Informe um e-mail válido, como financeiro@cliente.com.br"
                           class="painel-input painel-focus-ring pl-10">
                </div>
                <p class="mt-1 text-sm painel-text-secondary">Endereço que vai receber os boletos</p>
            </div>

            <!-- Nome do contato -->
            <div>
                <label for="nome_contato" class="painel-label">
                    Nome do Contato <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input type="text"
                           id="nome_contato"
                           name="nome_contato"
                           value="{{ old('nome_contato', $clienteEdit->nome_contato ?? '') }}"
                           placeholder="Pessoa responsável pelo pagamento"
                           required
                           maxlength="255"
                           class="painel-input painel-focus-ring pl-10">
                </div>
            </div>

            <div class="md:col-span-2">
                <h3 class="text-lg font-bold painel-text-primary">Dados para o boleto</h3>
                <p class="mt-1 text-sm painel-text-secondary">O Banco Inter exige estes dados para emitir a cobrança. Sem eles, o boleto não sai.</p>
            </div>

            <div>
                <label for="tipo_pessoa" class="painel-label">Tipo de pessoa</label>
                @php
                    $tipoSelecionado = old('tipo_pessoa', $clienteEdit->tipo_pessoa ?? '');
                @endphp
                <select id="tipo_pessoa" name="tipo_pessoa" class="painel-input painel-focus-ring">
                    <option value="">Selecione</option>
                    @foreach (\App\Enums\TipoPessoa::cases() as $tipo)
                        <option value="{{ $tipo->value }}" @selected($tipoSelecionado === $tipo->value)>{{ $tipo->rotulo() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                @php
                    $documento = preg_replace('/\D/', '', (string) old('cpf_cnpj', $clienteEdit->cpf_cnpj ?? '')) ?? '';
                    if (strlen($documento) === 11) {
                        $documento = substr($documento, 0, 3).'.'.substr($documento, 3, 3).'.'.substr($documento, 6, 3).'-'.substr($documento, 9, 2);
                    } elseif (strlen($documento) === 14) {
                        $documento = substr($documento, 0, 2).'.'.substr($documento, 2, 3).'.'.substr($documento, 5, 3).'/'.substr($documento, 8, 4).'-'.substr($documento, 12, 2);
                    }
                @endphp
                <label for="cpf_cnpj" class="painel-label">CPF ou CNPJ</label>
                <input type="text"
                       id="cpf_cnpj"
                       name="cpf_cnpj"
                       value="{{ $documento }}"
                       placeholder="000.000.000-00"
                       inputmode="numeric"
                       maxlength="18"
                       data-mascara="cpf-cnpj"
                       class="painel-input painel-focus-ring">
            </div>

            <div>
                @php
                    $cepExibido = (string) old('cep', $clienteEdit->cep ?? '');
                    $cepDigitos = preg_replace('/\D/', '', $cepExibido) ?? '';
                    if (strlen($cepDigitos) === 8) {
                        $cepExibido = substr($cepDigitos, 0, 5).'-'.substr($cepDigitos, 5);
                    }
                @endphp
                <label for="cep" class="painel-label">CEP</label>
                <input type="text"
                       id="cep"
                       name="cep"
                       inputmode="numeric"
                       autocomplete="postal-code"
                       value="{{ $cepExibido }}"
                       placeholder="00000-000"
                       maxlength="9"
                       data-mascara="cep"
                       data-consulta="{{ route('cep.consultar', ['cep' => '00000000']) }}"
                       class="painel-input painel-focus-ring">
                <p id="cep-aviso" class="mt-1 text-sm painel-text-secondary" aria-live="polite">Informe o CEP para preencher o endereço.</p>
            </div>

            <div class="md:col-span-2">
                <label for="endereco" class="painel-label">Endereço</label>
                <input type="text"
                       id="endereco"
                       name="endereco"
                       value="{{ old('endereco', $clienteEdit->endereco ?? '') }}"
                       maxlength="255"
                       class="painel-input painel-focus-ring">
            </div>

            <div>
                <label for="numero" class="painel-label">Número</label>
                <input type="text"
                       id="numero"
                       name="numero"
                       value="{{ old('numero', $clienteEdit->numero ?? '') }}"
                       placeholder="123 ou S/N"
                       maxlength="12"
                       data-mascara="numero"
                       class="painel-input painel-focus-ring">
            </div>

            <div>
                <label for="complemento" class="painel-label">Complemento</label>
                <input type="text"
                       id="complemento"
                       name="complemento"
                       value="{{ old('complemento', $clienteEdit->complemento ?? '') }}"
                       maxlength="100"
                       class="painel-input painel-focus-ring">
            </div>

            <div>
                <label for="bairro" class="painel-label">Bairro</label>
                <input type="text"
                       id="bairro"
                       name="bairro"
                       value="{{ old('bairro', $clienteEdit->bairro ?? '') }}"
                       maxlength="100"
                       class="painel-input painel-focus-ring">
            </div>

            <div>
                <label for="cidade" class="painel-label">Cidade</label>
                <input type="text"
                       id="cidade"
                       name="cidade"
                       value="{{ old('cidade', $clienteEdit->cidade ?? '') }}"
                       maxlength="100"
                       class="painel-input painel-focus-ring">
            </div>

            <div>
                <label for="uf" class="painel-label">UF</label>
                <input type="text"
                       id="uf"
                       name="uf"
                       value="{{ old('uf', $clienteEdit->uf ?? '') }}"
                       maxlength="2"
                       data-mascara="uf"
                       class="painel-input painel-focus-ring">
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-4 mt-8 pt-6 painel-divider-top">
            <a href="{{ route('clientes.index') }}" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $clienteEdit ? 'Atualizar Cliente' : 'Cadastrar Cliente' }}
            </button>
        </div>
    </form>
@endsection

@push('scripts-rodape')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cep = document.getElementById('cep');
            const aviso = document.getElementById('cep-aviso');

            if (!cep || !aviso || !cep.dataset.consulta) {
                return;
            }

            let ultimo = '';

            function preencher(id, valor) {
                const campo = document.getElementById(id);

                if (campo && valor !== '') {
                    campo.value = valor;
                }
            }

            async function consultar(digitos) {
                if (digitos === ultimo) {
                    return;
                }

                ultimo = digitos;
                aviso.textContent = 'Buscando endereço...';

                try {
                    const resposta = await fetch(cep.dataset.consulta.replace('00000000', digitos), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const dados = await resposta.json();

                    if (!resposta.ok) {
                        aviso.textContent = dados.message || 'CEP não encontrado.';
                        return;
                    }

                    preencher('endereco', dados.endereco || '');
                    preencher('bairro', dados.bairro || '');
                    preencher('cidade', dados.cidade || '');
                    preencher('uf', dados.uf || '');
                    aviso.textContent = dados.endereco
                        ? 'Endereço preenchido. Informe o número.'
                        : 'CEP encontrado. Complete o endereço.';
                    document.getElementById('numero')?.focus();
                } catch (erro) {
                    ultimo = '';
                    aviso.textContent = 'Não foi possível consultar o CEP.';
                }
            }

            function digitosDoCampo() {
                const digitos = cep.value.replace(/\D/g, '').slice(0, 8);
                cep.value = digitos.length > 5 ? digitos.slice(0, 5) + '-' + digitos.slice(5) : digitos;

                return digitos;
            }

            cep.addEventListener('input', function () {
                const digitos = digitosDoCampo();

                if (digitos.length < 8) {
                    ultimo = '';
                    aviso.textContent = 'Informe o CEP para preencher o endereço.';
                    return;
                }

                consultar(digitos);
            });
        });
    </script>
@endpush
