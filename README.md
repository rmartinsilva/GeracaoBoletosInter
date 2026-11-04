# Geração de Boletos Inter

Aplicação web em Laravel para gerenciar clientes e cobranças mensais (assinaturas) e integrar com a API de Cobranças do Banco Inter (boleto com Pix).

## O que já funciona (e o que ainda não funciona)

O painel já cobre o cadastro e o acompanhamento das cobranças:

- Painel administrativo em `/painel` com login por `login` (não por e-mail), grupos de usuário e permissões por chave (`usuario_*` e `cliente_*`).
- Cadastro de usuários e de clientes (nome, e-mail, contato e os dados do pagador: tipo de pessoa, CPF ou CNPJ, endereço, cidade, UF e CEP).
- Cadastro de assinaturas: descrição, valor, periodicidade (mensal ou anual), dia de vencimento, dia de geração, data de início e de fim, e situação (ativa ou encerrada). A cobrança avulsa fica na ficha do cliente e emite o boleto na hora, só com descrição, valor e vencimento.
- Geração dos lançamentos de cada período a partir da assinatura, com registro de pagamento e cancelamento de lançamento. Com o webhook cadastrado, o Banco Inter também quita o lançamento quando a cobrança é recebida.
- Emissão do boleto no dia de geração e envio do PDF por e-mail quando o Banco Inter devolve o boleto. O texto desse e-mail é único e fica em `/painel/cobranca/email`.

A integração com o Banco Inter autentica e conversa com a API: o cliente HTTP em `app/Services/Inter` emite, consulta, lista, edita, cancela, baixa o PDF e cadastra webhook de cobrança. Quem dispara isso no dia a dia é o comando `php artisan cobrancas:gerar`. O retorno do banco entra em `POST /inter/cobrancas/webhook` e quita o lançamento quando a cobrança é recebida.

## Tecnologias

- PHP 8.4
- Laravel 13
- Tailwind CSS pelo CDN no painel; a landing page usa CSS estático em `public/assets/css/site.css`
- MySQL
- PHPUnit 12

## Requisitos e instalação

Requisitos: PHP 8.4, Composer e MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

A aplicação usa MySQL. O `.env.example` já aponta para o banco `bancointer` em `127.0.0.1:3306`. Crie esse banco antes de migrar e preencha `DB_USERNAME` e `DB_PASSWORD` no `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bancointer
DB_USERNAME=root
DB_PASSWORD=
```

Para subir a aplicação:

```bash
php artisan serve
```

O site público fica em `http://localhost:8000` e o painel em `http://localhost:8000/painel`. O CSS já está em `public/assets/`; a página abre sem compilação de front-end. A fila, quando for processar e-mails, sobe com `php artisan queue:listen --tries=1`.

## Acesso ao painel

Depois de `php artisan migrate --seed`, o usuário administrador entra com:

- Login: `admin`
- Senha: `admin123`

> **Troque essa senha imediatamente** antes de usar a aplicação fora da sua máquina. As permissões do usuário valem a partir do próximo login depois de qualquer mudança nos grupos dele.

Rotas principais:

- `/` — landing page pública.
- `/painel/login` e `/painel/logout` — autenticação do painel.
- `/painel` — dashboard (requer sessão autenticada no guard `painel`).
- `/painel/usuarios`, `/painel/clientes` — cadastros (protegidos por permissão).
- `/painel/cobranca/email` — texto do e-mail enviado com o boleto (quem tem permissão `cliente_editar`).
- `/painel/clientes/{cliente}/assinaturas/...` — assinaturas e lançamentos do cliente.
- `POST /inter/cobrancas/webhook` — aviso do Banco Inter (paga, cancelada ou expirada). Não pede login.

## Fuso horário

Os horários são gravados em UTC e convertidos para `America/Sao_Paulo` apenas na exibição (`APP_DISPLAY_TIMEZONE`).

## Configuração do Banco Inter

A API usada é a Cobrança v3 (boleto com Pix). A v2 está descontinuada. Documentação: <https://developers.inter.co/docs/introducao/nossas-apis> e referência da API: <https://developers.inter.co/references/cobranca-bolepix>.

### Sandbox

1. Crie um cadastro no portal de desenvolvedores: <https://developers.inter.co/>.
2. Crie a integração nesse portal.
3. Baixe o certificado (`.crt`) e a chave privada (`.key`) gerados para essa integração.
4. Copie o `Client ID` e o `Client Secret` da integração.

Sem essa integração de sandbox, o comando `php artisan inter:verificar` não consegue autenticar.

### Produção

A integração de produção **não** é feita no portal de desenvolvedores. Ela é criada na conta PJ, pelo Internet Banking da conta pessoa jurídica do Banco Inter. De lá saem **outro** certificado, **outra** chave, **outro** Client ID e **outro** Client Secret.

Sandbox e produção **não compartilham** credenciais nem certificados: um certificado de produção é recusado pelo sandbox, e vice-versa. Se você for usar os dois ambientes, guarde dois pares de arquivos e dois pares de credenciais e alterne entre eles no `.env`.

### Variáveis de ambiente (`INTER_*`)

```dotenv
INTER_AMBIENTE=sandbox                 # sandbox ou producao — é essa variável que troca o ambiente
INTER_CLIENT_ID=                       # Client ID do ambiente escolhido
INTER_CLIENT_SECRET=                   # Client Secret do ambiente escolhido
INTER_CERTIFICADO=storage/app/inter/certificado.crt
INTER_CHAVE=storage/app/inter/chave.key
INTER_CERTIFICADO_SENHA=
INTER_CONTA_CORRENTE=
INTER_ESCOPOS="boleto-cobranca.read boleto-cobranca.write"
```

Detalhes importantes:

- `INTER_CERTIFICADO` e `INTER_CHAVE` apontam para o certificado e a chave **daquele ambiente**. Aceitam caminho absoluto ou relativo à raiz do projeto (nos exemplos acima, `storage/app/inter/`). Esses arquivos não devem entrar no git.
- `INTER_CERTIFICADO_SENHA` fica vazia: o Inter não entrega senha de certificado. O campo só é usado se a sua chave privada tiver passphrase.
- `INTER_CONTA_CORRENTE` só é necessária quando a aplicação enxerga mais de uma conta no Inter. Formato: só números, com o dígito verificador, sem zeros à esquerda (vai no cabeçalho `x-conta-corrente`).
- Os escopos padrão são `boleto-cobranca.read` e `boleto-cobranca.write`.
- O token OAuth dura 60 minutos e é reutilizado via cache. O token e o segredo nunca são impressos em logs ou na saída de comandos.
- O `config/inter.php` escolhe URL base, endpoint OAuth e certificado conforme `INTER_AMBIENTE`. **Depois de rodar `php artisan config:cache`, trocar de ambiente exige `php artisan config:clear`** — caso contrário a configuração antiga (e o certificado antigo) continua em cache.

### Conferindo a integração

```bash
php artisan inter:verificar
```

O comando autentica no ambiente configurado em `INTER_AMBIENTE` e informa o resultado. Ele não imprime o token nem o segredo. A primeira linha precisa ser o ambiente em que você vai cadastrar o webhook (`Ambiente: Sandbox` ou `Ambiente: Produção`).

### Webhook de pagamento

O Internet Banking não tem campo para a URL do webhook. O arquivo que ele oferece para baixar é o certificado `ca.crt`, a chave pública com que o Inter se identifica ao chamar o seu servidor. Esta aplicação recebe o aviso por HTTPS e não usa esse arquivo. A URL é cadastrada na API, com o mesmo certificado, Client ID e Client Secret que emitem o boleto. Referência: <https://developers.inter.co/references/cobranca-bolepix#tag/Webhook>.

Sandbox e produção são cadastros separados. Rode os comandos no servidor daquele ambiente, depois que o site estiver publicado em HTTPS. O caminho é fixo: `/inter/cobrancas/webhook`.

> **`boletos.exemplo.com.br` não é um endereço real.** É só um exemplo. Nos comandos abaixo, troque pelo domínio em que o **seu** sistema está publicado, como `https://cobrancas.suaempresa.com.br`. O trecho `/inter/cobrancas/webhook` fica igual. Se cadastrar o exemplo, o Banco Inter vai avisar um site que não é o seu.

1. Confirme que o endereço já responde, trocando o domínio pelo seu:

```bash
curl -s -o /dev/null -w "%{http_code}\n" -X POST \
  -H "Content-Type: application/json" \
  -d "[]" \
  https://boletos.exemplo.com.br/inter/cobrancas/webhook
```

A resposta tem de ser `200`. Se for `404`, publique esta versão antes de cadastrar. O Inter repete o aviso em 20, 30, 60 e 120 minutos quando o endereço não responde com sucesso.

2. Confirme o ambiente. Pare se não for o da integração que emite os boletos:

```bash
php artisan inter:verificar
```

3. Cadastre a URL. Troque `boletos.exemplo.com.br` pelo seu domínio antes de rodar. O `PUT` da API usa o escopo `boleto-cobranca.write`, que já está em `INTER_ESCOPOS`:

```bash
php artisan tinker --execute="app(\App\Services\Inter\Contracts\WebhookCobrancaInterInterface::class)->cadastrar('https://boletos.exemplo.com.br/inter/cobrancas/webhook');"
```

O comando não imprime nada quando o Inter aceita (resposta `204`).

4. Confira o que ficou gravado:

```bash
php artisan tinker --execute="dump(app(\App\Services\Inter\Contracts\WebhookCobrancaInterInterface::class)->obter());"
```

O `webhookUrl` tem de ser exatamente a URL do passo 3. `criacao` e `atualizacao` vêm no horário de Brasília.

A partir daí, o Inter avisa cobrança recebida, cancelada ou expirada. Recebida, ou marcada como recebida, quita o lançamento na data e hora do aviso. Cancelada encerra o que ainda está em aberto e não desfaz um pagamento já gravado. Expirada deixa o lançamento em aberto. O mesmo aviso não troca a data de um pagamento. Cobrança que o sistema não encontra é ignorada. Boleto pago antes desse cadastro não é reenviado.

Cadastrar de novo substitui a URL anterior. Há uma URL por integração.

### Emissão diária

```bash
php artisan cobrancas:gerar
```

No dia de geração da assinatura, o comando pede o boleto ao Inter. A emissão é assíncrona: se o PDF ainda não existe, a próxima execução consulta de novo e só então envia o e-mail, com o PDF em anexo. Não emite a mesma cobrança duas vezes.

Mensal e anual precisam do dia de geração. Se esse dia for posterior ao vencimento, o boleto sai no mês anterior. O dia 31 acompanha o último dia do mês. A cobrança avulsa não entra nessa rotina: na ficha do cliente, o boleto é emitido ao salvar, com a data de vencimento informada. Sem CPF ou CNPJ e endereço completo no cliente, o lançamento fica com o erro e o banco não é chamado. Se o campo "E-mail de falha" estiver preenchido em `/painel/cobranca/email`, esse endereço recebe um aviso. O mesmo motivo não é reenviado nas execuções seguintes.

O comando está agendado para todo dia às 08:00, horário de Brasília. No servidor, o cron precisa chamar `php artisan schedule:run` a cada minuto. Quem pode editar clientes também dispara a mesma rotina no botão "Gerar cobranças agora", no dashboard. Uma cobrança de assinatura que já falhou é tentada de novo nas execuções seguintes. O e-mail usa o mailer configurado em `MAIL_*`.

### Importar clientes já cobrados

```bash
php artisan clientes:importar
php artisan clientes:importar --de=2021-01-01 --ate=2026-11-03 --por=emissao
```

O comando lê as cobranças do ambiente em `INTER_AMBIENTE` e cadastra cada pagador que ainda não existe, usando o CPF ou CNPJ para não duplicar. O padrão percorre os últimos cinco anos pela data de emissão. `--por` aceita `emissao`, `vencimento` ou `pagamento`. Quem não tiver e-mail no Inter entra mesmo assim, e o endereço precisa ser preenchido no painel antes de enviar um boleto. A importação não cria assinatura.

## Testes

Os testes não usam o MySQL do `.env`. Eles rodam em SQLite em memória, para não alterar o banco da aplicação:

```bash
composer test          # limpa o cache de config e roda a suíte inteira
php artisan test       # equivalente, sem limpar o cache
php artisan test tests/Feature/PainelAssinaturaTest.php   # um arquivo
php artisan test --filter=test_formulario_de_assinatura_lista_as_periodicidades  # um teste
```

## Estrutura do projeto

```
app/
├── Console/Commands/       # inter:verificar e cobrancas:gerar
├── DTO/                    # DTOs de criação/atualização (ex.: CreateAssinaturaDTO)
├── Enums/                  # Periodicidade, SituacaoAssinatura, SituacaoLancamento, enums de Inter
├── Http/
│   ├── Controllers/Painel/ # Autenticacao, Usuario, Cliente, Assinatura, e-mail da cobrança, Configuracao
│   ├── Controllers/Site/   # landing pública
│   └── Requests/Painel/    # Form Requests de validação do painel
├── Models/                 # Usuario, Grupo, Acesso, Cliente, Assinatura, Lancamento
├── Repositories/           # Contracts (interfaces) e implementações Eloquent
├── Services/
│   ├── Inter/              # cliente HTTP da API Cobrança v3 do Banco Inter
│   ├── EmissorDeCobrancas.php
│   ├── AssinaturaService.php
│   └── DataHoraService.php # conversão UTC <-> America/Sao_Paulo
└── Support/
```

Camadas: Controller → Service → interface de repositório (`app/Repositories/Contracts/`) → implementação Eloquent (`app/Repositories/Eloquent/`). Regras de negócio ficam nos services; acesso a dados, só nos repositórios.

No painel:

- **Usuários** — cadastro com vínculo a grupos; cada grupo tem acessos (permissões) por chave, como `usuario_editar` e `cliente_visualizar`.
- **Clientes** — nome, e-mail, contato e os dados do pagador exigidos pelo boleto, com assinaturas vinculadas.
- **Assinaturas** — descrição, valor (formato brasileiro, ex. `1.500,00`), periodicidade (`mensal` ou `anual`), dia de vencimento e dia de geração (1–31), data de início, data de fim e situação (`ativa`/`encerrada`). Encerrar a assinatura cancela os lançamentos abertos com vencimento posterior à data de fim. A cobrança avulsa é outro cadastro, na ficha do cliente.
- **Lançamentos** — gerados por período a partir da assinatura, com situações aberto, pago e cancelado, registro de pagamento e, depois da emissão, a linha digitável, o Pix e a data do e-mail.
- **E-mail da cobrança** — texto padrão, com os marcadores `{nome}`, `{descricao}`, `{valor}`, `{vencimento}`, `{linha_digitavel}` e `{pix}`.

O tema claro/escuro do usuário fica em `configuracao_usuario` e é alternado via AJAX em `/painel/configuracao/toggle-tema`.
