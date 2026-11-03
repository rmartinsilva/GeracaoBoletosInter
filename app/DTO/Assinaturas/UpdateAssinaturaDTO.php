<?php

namespace App\DTO\Assinaturas;

use App\Enums\SituacaoAssinatura;
use App\Http\Requests\Painel\UpdateAssinaturaRequest;

class UpdateAssinaturaDTO
{
    public function __construct(
        public int $id,
        public string $descricao,
        public string $valor,
        public ?int $dia_vencimento,
        public ?int $dia_geracao,
        public SituacaoAssinatura $situacao,
        public ?string $data_fim,
    ) {}

    public static function makeFromRequest(UpdateAssinaturaRequest $request, int $id): self
    {
        $dia = $request->validated('dia_vencimento');
        $geracao = $request->validated('dia_geracao');
        $situacao = SituacaoAssinatura::from($request->validated('situacao'));

        return new self(
            $id,
            $request->validated('descricao'),
            (string) $request->validated('valor'),
            $dia === null ? null : (int) $dia,
            $geracao === null ? null : (int) $geracao,
            $situacao,
            $situacao === SituacaoAssinatura::Ativa ? null : $request->validated('data_fim'),
        );
    }
}
