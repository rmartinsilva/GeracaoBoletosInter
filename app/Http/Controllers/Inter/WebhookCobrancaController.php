<?php

namespace App\Http\Controllers\Inter;

use App\Http\Controllers\Controller;
use App\Services\ReceptorDeWebhookCobranca;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookCobrancaController extends Controller
{
    public function receber(Request $request, ReceptorDeWebhookCobranca $receptor): Response
    {
        $conteudo = $request->getContent();

        if (trim($conteudo) === '') {
            return response('', 200);
        }

        $corpo = json_decode($conteudo, true);

        if (! is_array($corpo)) {
            return response('O aviso do Banco Inter precisa ser uma lista.', 400);
        }

        $receptor->receber($corpo);

        return response('', 200);
    }
}
