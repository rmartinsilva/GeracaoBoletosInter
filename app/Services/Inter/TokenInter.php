<?php

namespace App\Services\Inter;

class TokenInter
{
    public function __construct(
        public readonly string $acesso,
        public readonly int $expiraEm,
        public readonly string $escopos,
        public readonly bool $reutilizado,
    ) {}
}
