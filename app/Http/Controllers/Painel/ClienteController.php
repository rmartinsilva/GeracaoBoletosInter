<?php

namespace App\Http\Controllers\Painel;

use App\DTO\Clientes\CreateClienteDTO;
use App\DTO\Clientes\UpdateClienteDTO;
use App\Enums\SituacaoCliente;
use App\Exceptions\RegraNegocioException;
use App\Http\Requests\Painel\StoreClienteRequest;
use App\Http\Requests\Painel\UpdateClienteRequest;
use App\Services\AssinaturaService;
use App\Services\ClienteService;
use App\Services\CobrancaAvulsaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends PainelController
{
    public function __construct(
        protected Request $request,
        protected ClienteService $clienteService,
        protected AssinaturaService $assinaturaService,
        protected CobrancaAvulsaService $cobrancaAvulsaService,
    ) {
        parent::__construct($request);
    }

    /**
     * Lista os clientes, com busca por nome, e-mail ou contato.
     */
    public function index(): View
    {
        $this->checkPermissionOrFail('cliente_visualizar');

        $search = $this->request->string('search')->trim()->value();
        $situacao = SituacaoCliente::tryFrom($this->request->string('situacao')->trim()->value());

        $clientes = $this->clienteService->listarClientes(
            pagina: $this->request->integer('page', 1),
            porPagina: 15,
            filtro: $search !== '' ? $search : null,
            situacao: $situacao,
        );

        return view('painel.clientes.index', [
            'titulo' => 'Clientes',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'clientes' => $clientes,
            'search' => $search,
            'situacao' => $situacao?->value,
        ]);
    }

    /**
     * Exibe o formulário de cadastro.
     */
    public function create(): View
    {
        $this->checkPermissionOrFail('cliente_cadastrar');

        return view('painel.clientes.form', [
            'titulo' => 'Novo Cliente',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'clienteEdit' => null,
        ]);
    }

    /**
     * Salva um novo cliente.
     */
    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_cadastrar');

        try {
            $cliente = $this->clienteService->criarCliente(CreateClienteDTO::makeFromRequest($request));

            return redirect()->route('clientes.show', $cliente->id)
                ->with('success', 'Cliente cadastrado com sucesso!');
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao cadastrar cliente: '.$e->getMessage()]);
        }
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        $clienteEdit = $this->clienteService->buscarPorId($id);

        if (! $clienteEdit) {
            return redirect()->route('clientes.index')
                ->withErrors(['error' => 'Cliente não encontrado.']);
        }

        return view('painel.clientes.form', [
            'titulo' => 'Editar Cliente',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'clienteEdit' => $clienteEdit,
        ]);
    }

    /**
     * Atualiza um cliente existente.
     */
    public function update(UpdateClienteRequest $request, int $id): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        try {
            $cliente = $this->clienteService->atualizarCliente(UpdateClienteDTO::makeFromRequest($request, $id));

            if (! $cliente) {
                return redirect()->route('clientes.index')
                    ->withErrors(['error' => 'Cliente não encontrado.']);
            }

            return redirect()->route('clientes.show', $cliente->id)
                ->with('success', 'Cliente atualizado com sucesso!');
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar cliente: '.$e->getMessage()]);
        }
    }

    /**
     * Deleta um cliente.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_deletar');

        try {
            if ($this->clienteService->deletarCliente($id)) {
                return redirect()->route('clientes.index')
                    ->with('success', 'Cliente deletado com sucesso!');
            }

            return redirect()->route('clientes.index')
                ->withErrors(['error' => 'Cliente não encontrado.']);
        } catch (RegraNegocioException $e) {
            return redirect()->route('clientes.index')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('clientes.index')
                ->withErrors(['error' => 'Erro ao deletar cliente: '.$e->getMessage()]);
        }
    }

    /**
     * Ficha do cliente, com as assinaturas e o caminho para os lançamentos.
     */
    public function show(int $id): View|RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_visualizar');

        $cliente = $this->clienteService->buscarPorId($id);

        if (! $cliente) {
            return redirect()->route('clientes.index')
                ->withErrors(['error' => 'Cliente não encontrado.']);
        }

        return view('painel.clientes.show', [
            'titulo' => $cliente->nome,
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'cliente' => $cliente,
            'assinaturas' => $this->assinaturaService->listarPorCliente($id),
            'cobrancasAvulsas' => $this->cobrancaAvulsaService->listar($id),
        ]);
    }

    /**
     * Inativa o cliente e interrompe as assinaturas ativas.
     */
    public function inativar(int $id): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        try {
            $encerradas = $this->clienteService->inativar($id);

            if ($encerradas === null) {
                return redirect()->route('clientes.index')
                    ->withErrors(['error' => 'Cliente não encontrado.']);
            }

            return redirect()->route('clientes.show', $id)
                ->with('success', $this->mensagemInativacao($encerradas));
        } catch (RegraNegocioException $e) {
            return redirect()->route('clientes.show', $id)
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('clientes.show', $id)
                ->withErrors(['error' => 'Erro ao inativar cliente: '.$e->getMessage()]);
        }
    }

    /**
     * Reativa o cliente. As assinaturas encerradas não voltam.
     */
    public function reativar(int $id): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        try {
            if (! $this->clienteService->reativar($id)) {
                return redirect()->route('clientes.index')
                    ->withErrors(['error' => 'Cliente não encontrado.']);
            }

            return redirect()->route('clientes.show', $id)
                ->with('success', 'Cliente reativado. As assinaturas encerradas continuam encerradas; cadastre uma nova se a cobrança voltar.');
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('clientes.show', $id)
                ->withErrors(['error' => 'Erro ao reativar cliente: '.$e->getMessage()]);
        }
    }

    private function mensagemInativacao(int $encerradas): string
    {
        return match ($encerradas) {
            0 => 'Cliente inativado. Nenhuma assinatura ativa precisou ser encerrada.',
            1 => 'Cliente inativado. A assinatura ativa foi encerrada e os lançamentos com vencimento posterior foram cancelados.',
            default => "Cliente inativado. {$encerradas} assinaturas ativas foram encerradas e os lançamentos com vencimento posterior foram cancelados.",
        };
    }
}
