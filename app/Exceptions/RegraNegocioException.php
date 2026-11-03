<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Regra de negócio que deve ser mostrada ao usuário, sem ser tratada como falha inesperada.
 */
class RegraNegocioException extends RuntimeException {}
