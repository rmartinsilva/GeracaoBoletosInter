<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Container\Attributes\Config;

class DataHoraService
{
    public function __construct(
        #[Config('app.display_timezone')] protected string $fusoDeExibicao
    ) {}

    /**
     * Retorna o momento atual no fuso de exibição.
     * Use para saber qual é a data de "hoje" no Brasil.
     */
    public function agora(): CarbonImmutable
    {
        return CarbonImmutable::now($this->fusoDeExibicao);
    }

    /**
     * Converte um instante gravado em UTC para o fuso de exibição.
     */
    public function paraExibicao(CarbonInterface $instante): CarbonImmutable
    {
        return CarbonImmutable::instance($instante)->setTimezone($this->fusoDeExibicao);
    }

    /**
     * Interpreta uma data/hora digitada no fuso de exibição e a converte para UTC,
     * pronta para persistir.
     */
    public function paraUtc(string $dataHora): CarbonImmutable
    {
        return CarbonImmutable::parse($dataHora, $this->fusoDeExibicao)->utc();
    }

    /**
     * Formata um instante como "d/m/Y H:i" no fuso de exibição.
     */
    public function formatarDataHora(?CarbonInterface $instante): string
    {
        if ($instante === null) {
            return '';
        }

        return $this->paraExibicao($instante)->format('d/m/Y H:i');
    }

    /**
     * Formata uma data de calendário (como um vencimento) como "d/m/Y",
     * sem conversão de fuso.
     */
    public function formatarData(?CarbonInterface $data): string
    {
        if ($data === null) {
            return '';
        }

        return $data->format('d/m/Y');
    }
}
