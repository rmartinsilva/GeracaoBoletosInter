@extends('template.painel')

@section('conteudo')
    <div class="painel-card p-6 mb-6">
        <div>
            <h2 class="text-2xl font-bold painel-text-primary">E-mail da cobrança</h2>
            <p class="painel-text-secondary mt-1">
                Este texto é enviado ao cliente assim que o boleto fica pronto. Vale para todas as assinaturas.
            </p>
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

    <form action="{{ route('cobranca.email.update') }}" method="POST" class="painel-card p-6">
        @csrf
        @method('PUT')

        <label for="email_falha" class="painel-label">E-mail de falha</label>
        <input type="email"
               id="email_falha"
               name="email_falha"
               value="{{ old('email_falha', $emailFalha) }}"
               maxlength="255"
               placeholder="financeiro@empresa.com.br"
               class="painel-input painel-focus-ring">
        <p class="mt-2 text-sm painel-text-secondary">
            Recebe um aviso quando a emissão do boleto falha. O mesmo motivo não é reenviado nas execuções seguintes.
        </p>

        <label for="corpo_email" class="painel-label mt-8">
            Texto do e-mail <span class="text-red-500">*</span>
        </label>
        <textarea id="corpo_email"
                  name="corpo_email"
                  rows="16"
                  required
                  maxlength="5000"
                  class="painel-input painel-focus-ring">{{ old('corpo_email', $corpo) }}</textarea>
        <p class="mt-2 text-sm painel-text-secondary">
            Marcadores: {nome}, {descricao}, {valor}, {vencimento}, {linha_digitavel} e {pix}. O PDF do boleto vai em anexo.
        </p>

        <div class="flex items-center justify-end gap-4 mt-8 pt-6 painel-divider-top">
            <button type="submit" class="btn-primary inline-flex items-center">
                Salvar
            </button>
        </div>
    </form>
@endsection
