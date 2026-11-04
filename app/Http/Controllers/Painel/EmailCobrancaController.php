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

        $configuracao = ConfiguracaoCobranca::atual();

        return view('painel.cobranca.email', [
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'corpo' => $configuracao->corpo_email,
            'emailFalha' => $configuracao->email_falha,
            'emailCopia' => $configuracao->email_copia,
        ]);
    }

    public function update(UpdateEmailCobrancaRequest $request): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        $configuracao = ConfiguracaoCobranca::atual();
        $configuracao->update([
            'corpo_email' => $request->validated('corpo_email'),
            'email_falha' => $request->validated('email_falha'),
            'email_copia' => $request->validated('email_copia'),
        ]);

        return redirect()
            ->route('cobranca.email.edit')
            ->with('success', 'Configuração da cobrança atualizada com sucesso!');
    }
}
