<?php

namespace App\Http\Controllers\Painel;

use App\Http\Requests\Painel\UpdateEmailCobrancaRequest;
use App\Models\ConfiguracaoCobranca;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailCobrancaController extends PainelController
{
    public function edit(): View
    {
        $this->checkPermissionOrFail('cliente_editar');

        return view('painel.cobranca.email', [
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'corpo' => ConfiguracaoCobranca::atual()->corpo_email,
        ]);
    }

    public function update(UpdateEmailCobrancaRequest $request): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        $configuracao = ConfiguracaoCobranca::atual();
        $configuracao->update([
            'corpo_email' => $request->validated('corpo_email'),
        ]);

        return redirect()
            ->route('cobranca.email.edit')
            ->with('success', 'Texto do e-mail atualizado com sucesso!');
    }
}
