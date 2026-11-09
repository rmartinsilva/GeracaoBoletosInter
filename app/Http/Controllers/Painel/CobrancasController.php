<?php

namespace App\Http\Controllers\Painel;

use App\Services\ListagemDeCobrancas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CobrancasController extends PainelController
{
    public function __construct(
        protected Request $request,
        protected ListagemDeCobrancas $listagem,
    ) {
        parent::__construct($request);
    }

    /**
     * Lista as cobranças do período para conferir emissão e baixa.
     */
    public function index(): View
    {
        $this->checkPermissionOrFail('cliente_visualizar');

        $filtro = $this->listagem->montarFiltro(
            de: $this->request->string('de')->trim()->value() ?: null,
            ate: $this->request->string('ate')->trim()->value() ?: null,
            busca: $this->request->string('busca')->trim()->value() ?: null,
            situacao: $this->request->string('situacao')->trim()->value() ?: null,
            emissao: $this->request->string('emissao')->trim()->value() ?: null,
        );

        return view('painel.cobrancas.index', [
            'titulo' => 'Cobranças',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'cobrancas' => $this->listagem->listar($filtro, $this->request->integer('page', 1)),
            'resumo' => $this->listagem->resumir($filtro),
            'filtro' => $filtro,
        ]);
    }
}
