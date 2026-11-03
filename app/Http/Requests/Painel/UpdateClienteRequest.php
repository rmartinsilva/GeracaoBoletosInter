<?php

namespace App\Http\Requests\Painel;

/**
 * A edição usa as mesmas regras do cadastro, já que o formulário sempre envia todos os campos.
 */
class UpdateClienteRequest extends StoreClienteRequest {}
