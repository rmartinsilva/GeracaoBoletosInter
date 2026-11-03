<?php

namespace App\Http\Controllers\Painel;

use App\DTO\Assinaturas\CreateAssinaturaDTO;
use App\DTO\Assinaturas\UpdateAssinaturaDTO;
use App\Enums\Periodicidade;
use App\Enums\SituacaoAssinatura;
use App\Exceptions\RegraNegocioException;
use App\Http\Requests\Painel\AtualizarLancamentoRequest;
use App\Http\Requests\Painel\RegistrarPagamentoRequest;
use App\Http\Requests\Painel\StoreAssinaturaRequest;
use App\Http\Requests\Painel\UpdateAssinaturaRequest;
use App\Services\AssinaturaService;
use App\Services\ClienteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use stdClass;

class AssinaturaController extends PainelController
{
    public function __construct(
        protected Request $request,
        protected AssinaturaService $assinaturaService,
        protected ClienteService $clienteService,
    ) {
        parent::__construct($request);
    }

    /**
     * Exibe o formulário de uma nova assinatura.
     */
    public function create(int $cliente): View|RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_cadastrar');

        $clienteEdit = $this->buscarCliente($cliente);

        if (! $clienteEdit) {
            return $this->clienteAusente();
        }

        return view('painel.assinaturas.form', [
            'titulo' => 'Nova Assinatura',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'cliente' => $clienteEdit,
            'assinaturaEdit' => null,
            'periodicidades' => Periodicidade::cases(),
        ]);
    }

    /**
     * Salva a assinatura e gera os lançamentos até o mês atual.
     */
    public function store(StoreAssinaturaRequest $request, int $cliente): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_cadastrar');

        if (! $this->buscarCliente($cliente)) {
            return $this->clienteAusente();
        }

        try {
            $resultado = $this->assinaturaService->criar(CreateAssinaturaDTO::makeFromRequest($request, $cliente));

            return redirect()
                ->route('assinaturas.show', [$cliente, $resultado['assinatura']->id])
                ->with('success', $this->mensagemCadastro($resultado['gerados'], $resultado['limitado']));
        } catch (RegraNegocioException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao cadastrar assinatura: '.$e->getMessage()]);
        }
    }

    /**
     * Histórico de lançamentos da assinatura.
     */
    public function show(int $cliente, int $assinatura): View|RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_visualizar');

        if (! $this->buscarCliente($cliente)) {
            return $this->clienteAusente();
        }

        $assinaturaEdit = $this->assinaturaService->buscarParaExibicao($cliente, $assinatura);

        if (! $assinaturaEdit) {
            return $this->assinaturaAusente($cliente);
        }

        return view('painel.assinaturas.show', [
            'titulo' => $assinaturaEdit->descricao,
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'assinatura' => $assinaturaEdit,
        ]);
    }

    /**
     * Exibe o formulário de edição da assinatura.
     */
    public function edit(int $cliente, int $assinatura): View|RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        $clienteEdit = $this->buscarCliente($cliente);

        if (! $clienteEdit) {
            return $this->clienteAusente();
        }

        $assinaturaEdit = $this->assinaturaDoCliente($cliente, $assinatura);

        if (! $assinaturaEdit) {
            return $this->assinaturaAusente($cliente);
        }

        return view('painel.assinaturas.form', [
            'titulo' => 'Editar Assinatura',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'cliente' => $clienteEdit,
            'assinaturaEdit' => $assinaturaEdit,
            'periodicidades' => Periodicidade::cases(),
            'situacoes' => SituacaoAssinatura::cases(),
        ]);
    }

    /**
     * Atualiza a assinatura. O histórico de lançamentos já gerados é mantido.
     */
    public function update(UpdateAssinaturaRequest $request, int $cliente, int $assinatura): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        if (! $this->buscarCliente($cliente)) {
            return $this->clienteAusente();
        }

        if (! $this->assinaturaDoCliente($cliente, $assinatura)) {
            return $this->assinaturaAusente($cliente);
        }

        try {
            $atualizada = $this->assinaturaService->atualizar(UpdateAssinaturaDTO::makeFromRequest($request, $assinatura));

            if (! $atualizada) {
                return $this->assinaturaAusente($cliente);
            }

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->with('success', 'Assinatura atualizada com sucesso!');
        } catch (RegraNegocioException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar assinatura: '.$e->getMessage()]);
        }
    }

    /**
     * Exclui a assinatura quando ainda não há pagamento registrado.
     */
    public function destroy(int $cliente, int $assinatura): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_deletar');

        if (! $this->buscarCliente($cliente)) {
            return $this->clienteAusente();
        }

        if (! $this->assinaturaDoCliente($cliente, $assinatura)) {
            return $this->assinaturaAusente($cliente);
        }

        try {
            if ($this->assinaturaService->deletar($assinatura)) {
                return redirect()
                    ->route('clientes.show', $cliente)
                    ->with('success', 'Assinatura excluída com sucesso!');
            }

            return $this->assinaturaAusente($cliente);
        } catch (RegraNegocioException $e) {
            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withErrors(['error' => 'Erro ao excluir assinatura: '.$e->getMessage()]);
        }
    }

    /**
     * Gera o lançamento do próximo período.
     */
    public function gerarProximo(int $cliente, int $assinatura): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        if (! $this->assinaturaDoCliente($cliente, $assinatura)) {
            return $this->assinaturaAusente($cliente);
        }

        try {
            $lancamento = $this->assinaturaService->gerarProximo($assinatura);

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->with('success', 'Lançamento de '.$lancamento->competencia->format('m/Y').' gerado.');
        } catch (RegraNegocioException $e) {
            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withErrors(['error' => 'Erro ao gerar lançamento: '.$e->getMessage()]);
        }
    }

    /**
     * Registra o pagamento de um lançamento em aberto.
     */
    public function registrarPagamento(RegistrarPagamentoRequest $request, int $cliente, int $assinatura, int $lancamento): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        if (! $this->assinaturaDoCliente($cliente, $assinatura)) {
            return $this->assinaturaAusente($cliente);
        }

        try {
            $this->assinaturaService->registrarPagamento($assinatura, $lancamento, $request->validated('data_pagamento'));

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->with('success', 'Pagamento registrado.');
        } catch (RegraNegocioException $e) {
            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withErrors(['error' => 'Erro ao registrar pagamento: '.$e->getMessage()]);
        }
    }

    /**
     * Cancela um lançamento em aberto, sem apagar o histórico.
     */
    public function cancelarLancamento(int $cliente, int $assinatura, int $lancamento): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        if (! $this->assinaturaDoCliente($cliente, $assinatura)) {
            return $this->assinaturaAusente($cliente);
        }

        try {
            $this->assinaturaService->cancelarLancamento($assinatura, $lancamento);

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->with('success', 'Lançamento cancelado.');
        } catch (RegraNegocioException $e) {
            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withErrors(['error' => 'Erro ao cancelar lançamento: '.$e->getMessage()]);
        }
    }

    /**
     * Corrige valor e vencimento de um lançamento em aberto.
     */
    public function atualizarLancamento(AtualizarLancamentoRequest $request, int $cliente, int $assinatura, int $lancamento): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        if (! $this->assinaturaDoCliente($cliente, $assinatura)) {
            return $this->assinaturaAusente($cliente);
        }

        try {
            $this->assinaturaService->atualizarLancamento(
                $assinatura,
                $lancamento,
                (string) $request->validated('valor'),
                $request->validated('vencimento'),
            );

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->with('success', 'Lançamento atualizado.');
        } catch (RegraNegocioException $e) {
            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->route('assinaturas.show', [$cliente, $assinatura])
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar lançamento: '.$e->getMessage()]);
        }
    }

    private function buscarCliente(int $clienteId): ?stdClass
    {
        return $this->clienteService->buscarPorId($clienteId);
    }

    /**
     * Garante que a assinatura pertence ao cliente da URL.
     */
    private function assinaturaDoCliente(int $clienteId, int $assinaturaId): ?stdClass
    {
        $assinatura = $this->assinaturaService->buscarPorId($assinaturaId);

        if (! $assinatura || (int) $assinatura->cliente_id !== $clienteId) {
            return null;
        }

        return $assinatura;
    }

    private function clienteAusente(): RedirectResponse
    {
        return redirect()
            ->route('clientes.index')
            ->withErrors(['error' => 'Cliente não encontrado.']);
    }

    private function assinaturaAusente(int $clienteId): RedirectResponse
    {
        return redirect()
            ->route('clientes.show', $clienteId)
            ->withErrors(['error' => 'Assinatura não encontrada.']);
    }

    private function mensagemCadastro(int $gerados, bool $limitado): string
    {
        $texto = match ($gerados) {
            0 => 'Assinatura cadastrada com sucesso! Nenhum lançamento foi gerado.',
            1 => 'Assinatura cadastrada com sucesso! Foi gerado 1 lançamento.',
            default => "Assinatura cadastrada com sucesso! Foram gerados {$gerados} lançamentos.",
        };

        if ($limitado) {
            $texto .= ' Entraram só os 120 lançamentos mais recentes.';
        }

        return $texto;
    }
}
