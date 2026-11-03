<?php

namespace App\Http\Controllers\Painel;

use App\Enums\SituacaoCliente;
use App\Exceptions\RegraNegocioException;
use App\Http\Requests\Painel\StoreCobrancaAvulsaRequest;
use App\Services\ClienteService;
use App\Services\CobrancaAvulsaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use stdClass;

class CobrancaAvulsaController extends PainelController
{
    public function __construct(
        protected Request $request,
        protected ClienteService $clienteService,
        protected CobrancaAvulsaService $cobrancaAvulsaService,
    ) {
        parent::__construct($request);
    }

    public function create(int $cliente): View|RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_cadastrar');

        $clienteEdit = $this->clienteService->buscarPorId($cliente);

        if (! $clienteEdit) {
            return $this->clienteAusente();
        }

        if ($this->clienteInativo($clienteEdit)) {
            return $this->clienteInativoRedireciona($cliente);
        }

        return view('painel.cobrancas.avulsa', [
            'titulo' => 'Nova cobrança avulsa',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'cliente' => $clienteEdit,
        ]);
    }

    public function store(StoreCobrancaAvulsaRequest $request, int $cliente): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_cadastrar');

        $clienteEdit = $this->clienteService->buscarPorId($cliente);

        if (! $clienteEdit) {
            return $this->clienteAusente();
        }

        if ($this->clienteInativo($clienteEdit)) {
            return $this->clienteInativoRedireciona($cliente);
        }

        try {
            $resumo = $this->cobrancaAvulsaService->criar(
                $cliente,
                $request->validated('descricao'),
                $request->validated('valor'),
                $request->validated('vencimento'),
            );
        } catch (RegraNegocioException $excecao) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $excecao->getMessage()]);
        }

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', $this->mensagem($resumo));
    }

    /**
     * @param  array{emitidas: int, enviadas: int, aguardando: int, falhas: int}  $resumo
     */
    private function mensagem(array $resumo): string
    {
        if ($resumo['falhas'] > 0) {
            return 'A cobrança foi registrada, mas a emissão falhou. O motivo aparece na lista e a rotina das 08:00 tenta de novo.';
        }

        if ($resumo['enviadas'] > 0) {
            return 'Cobrança avulsa emitida e o boleto foi enviado por e-mail.';
        }

        return 'Cobrança avulsa emitida. O boleto será enviado por e-mail assim que o Banco Inter liberar o PDF.';
    }

    private function clienteAusente(): RedirectResponse
    {
        return redirect()
            ->route('clientes.index')
            ->withErrors(['error' => 'Cliente não encontrado.']);
    }

    private function clienteInativo(stdClass $cliente): bool
    {
        return ($cliente->situacao ?? SituacaoCliente::Ativo->value) !== SituacaoCliente::Ativo->value;
    }

    private function clienteInativoRedireciona(int $clienteId): RedirectResponse
    {
        return redirect()
            ->route('clientes.show', $clienteId)
            ->withErrors(['error' => 'Cliente inativo não recebe cobrança nova. Reative o cliente antes.']);
    }
}
