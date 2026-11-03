<?php

/*
| Banco Inter — API Cobrança v3 (boleto com Pix).
|
| INTER_AMBIENTE troca sandbox e produção juntos: URL, OAuth e certificado.
| Com a configuração em cache, rode `php artisan config:clear` depois da troca.
|
| Sandbox:  https://cdpj-sandbox.partners.uatinter.co
| Produção: https://cdpj.partners.bancointer.com.br
*/

$urls = [
    'sandbox' => 'https://cdpj-sandbox.partners.uatinter.co',
    'producao' => 'https://cdpj.partners.bancointer.com.br',
];

$ambiente = env('INTER_AMBIENTE', 'sandbox');

return [
    'ambiente' => $ambiente,

    'url_base' => $urls[$ambiente] ?? null,

    'urls' => $urls,

    'client_id' => env('INTER_CLIENT_ID'),

    'client_secret' => env('INTER_CLIENT_SECRET'),

    'certificado' => env('INTER_CERTIFICADO'),

    'chave' => env('INTER_CHAVE'),

    'certificado_senha' => env('INTER_CERTIFICADO_SENHA'),

    'conta_corrente' => env('INTER_CONTA_CORRENTE'),

    'escopos' => env('INTER_ESCOPOS', 'boleto-cobranca.read boleto-cobranca.write'),

    'timeout' => (int) env('INTER_TIMEOUT', 30),

    'margem_token_segundos' => (int) env('INTER_MARGEM_TOKEN', 60),
];
