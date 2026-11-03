<?php

namespace App\Http\Controllers\Painel;

use App\Exceptions\RegraNegocioException;
use App\Services\ConsultaCep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CepController extends PainelController
{
    public function __construct(
        protected Request $request,
        private ConsultaCep $consultaCep,
    ) {
        parent::__construct($request);
    }

    /**
     * Devolve o endereço do CEP para preencher o cadastro do cliente.
     */
    public function show(string $cep): JsonResponse
    {
        if (! $this->hasPermission('cliente_cadastrar') && ! $this->hasPermission('cliente_editar')) {
            abort(403, 'Você não tem permissão para consultar o CEP.');
        }

        try {
            return response()->json($this->consultaCep->buscar($cep));
        } catch (RegraNegocioException $excecao) {
            return response()->json(['message' => $excecao->getMessage()], 422);
        }
    }
}
