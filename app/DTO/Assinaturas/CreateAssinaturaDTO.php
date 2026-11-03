<?php

namespace App\DTO\Assinaturas;

use App\Enums\Periodicidade;
use App\Http\Requests\Painel\StoreAssinaturaRequest;

class CreateAssinaturaDTO
{
    public function __construct(
        public int $cliente_id,
        public string $descricao,
        public string $valor,
        public Periodicidade $periodicidade,
        public ?int $dia_vencimento,
        public ?int $dia_geracao,
        public string $data_inicio,
    ) {}

    public static function makeFromRequest(StoreAssinaturaRequest $request, int $clienteId): self
    {
        $dia = $request->validated('dia_vencimento');
        $geracao = $request->validated('dia_geracao');

        return new self(
            $clienteId,
            $request->validated('descricao'),
            (string) $request->validated('valor'),
            Periodicidade::from($request->validated('periodicidade')),
            $dia === null ? null : (int) $dia,
            $geracao === null ? null : (int) $geracao,
            $request->validated('data_inicio'),
        );
    }
}
