# CLAUDE.md

Este arquivo orienta o Claude Code (claude.ai/code) ao trabalhar com o código deste repositório.

## Visão geral

Aplicação Laravel 13 / PHP 8.4 para gerar cobranças mensais (boletos) pelo Banco Inter. Há uma landing page pública e um painel administrativo (`/painel`) com login, permissões por grupo, cadastro de usuários, cadastro de clientes (nome, e-mail, contato, dados do pagador e situação ativo ou inativo) e de assinaturas, com os lançamentos de cada período. Inativar o cliente encerra as assinaturas ativas e cancela os lançamentos com vencimento posterior. A assinatura guarda o dia de vencimento e o dia de geração. O comando `cobrancas:gerar` emite o boleto nesse dia e envia o e-mail quando o PDF fica pronto. O dashboard repete essa rotina no botão "Gerar cobranças agora". Uma cobrança de assinatura que já falhou continua sendo tentada nas execuções seguintes, mesmo fora do dia de geração. O texto desse e-mail e o endereço que recebe aviso de falha ficam em `/painel/cobranca/email`. O comando `clientes:importar` cadastra os pagadores das cobranças do ambiente configurado que ainda não existem, sem criar assinatura. A cobrança avulsa não é assinatura: na ficha do cliente, descrição, valor e vencimento emitem o boleto na hora.

O código começou a partir de um template antigo de site. As sobras dele (portfólio, notícias, banners, "De Lollo", "TechPro") estão sendo removidas, e os testes de feature garantem que não voltem. O `SETUP.md` ainda descreve esse template em alguns pontos (por exemplo, lista 44 permissões, mas hoje só existem as chaves `usuario_*` e `cliente_*`). Na dúvida, confie no código.

Código de domínio, textos da interface, mensagens de validação e documentação são em português (`Usuario`, `Grupo`, `Acesso`, `listarUsuarios()`, `criarUsuario()`). Siga esse padrão ao escrever código novo ou documentação.

## Comandos de desenvolvimento

```bash
# Primeira instalação
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # o .env.example usa SQLite por padrão
php artisan migrate --seed       # cria o usuário admin: admin / admin123

# O PermissoesSeeder é idempotente: num banco já populado ele só cadastra as
# permissões que faltam e as vincula ao grupo Administrador (a senha do admin não muda).
php artisan db:seed

# Recriar o banco do zero
php artisan migrate:fresh --seed

# Desenvolvimento: php artisan serve + queue:listen + pail juntos
composer run dev

# Testes (sempre SQLite em memória, ver phpunit.xml)
composer test                    # limpa o cache de config e roda tudo
php artisan test tests/Feature/PainelDashboardTest.php
php artisan test --filter=test_dashboard_mostra_apenas_clientes_e_usuarios

# Formatação
vendor/bin/pint --dirty
```

## Arquitetura

### Camadas

Controller → Service (`app/Services/`) → interface do repositório (`app/Repositories/Contracts/`) → implementação Eloquent (`app/Repositories/Eloquent/`).

- Controllers ficam enxutos: validam e delegam ao service. Regra de negócio fica nos services e acesso a dados, só nos repositórios. Injete a interface, nunca o repositório concreto.
- Cada interface é ligada à sua implementação em `AppServiceProvider::register()`. Adicione o binding ali para cada repositório novo.
- Métodos paginados dos repositórios retornam `App\Repositories\PaginationPresenter` (que encapsula o `LengthAwarePaginator`), não o paginator.
- `ClienteController` → `ClienteService` → `ClienteRepository` é a implementação de referência para novos CRUDs. Ela segue a skill `.agent/skills/design_patterns`, adaptada ao painel Blade: Form Requests em `app/Http/Requests/Painel/`, DTOs `Create<Modulo>DTO`/`Update<Modulo>DTO` com `makeFromRequest()` em `app/DTO/<Modulos>/`, repositório devolvendo `stdClass` em `buscarPorId`/`criar`/`atualizar`, e enums em `app/Enums/`. O `UsuarioController` é anterior e ainda valida inline.
- Valores em dinheiro são gravados como `decimal(10, 2)` e digitados no formato brasileiro (`1.500,00`); o `App\Support\ValorMonetario` converte para `1500.00` antes de validar, a partir do `StoreAssinaturaRequest`.

### Integração com o Banco Inter

A API usada é a Cobrança v3 (boleto com Pix). A v2 foi descontinuada. O cliente HTTP fica em `App\Services\Inter`. Quem grava o boleto e dispara o e-mail é o `EmissorDeCobrancas`, chamado por `php artisan cobrancas:gerar` (agendado todo dia às 08:00, horário de Brasília) e pelo botão do dashboard. A emissão é assíncrona: se o PDF ainda não existe, a próxima execução consulta de novo e só então envia o e-mail. Uma falha de emissão grava o motivo no lançamento e, se houver e-mail de falha configurado, avisa esse endereço uma vez por motivo.

- `config/inter.php` escolhe o ambiente por `INTER_AMBIENTE` (`sandbox` ou `producao`). A URL base, o OAuth em `/oauth/v2/token` e o certificado mTLS acompanham essa escolha. Com a configuração em cache, rode `php artisan config:clear` depois de trocar o ambiente.
- O token dura 60 minutos, é reutilizado pelo cache e não é impresso. Os escopos padrão são `boleto-cobranca.read` e `boleto-cobranca.write`.
- Havendo mais de uma conta na aplicação, `INTER_CONTA_CORRENTE` vai no cabeçalho `x-conta-corrente` (só números, com o dígito, sem zeros à esquerda).
- `CobrancaInterService` emite, consulta, lista, resume, edita, cancela e baixa o PDF. `pagarSandbox()` simula o pagamento e só funciona no sandbox.
- `WebhookCobrancaInterService` cadastra a URL HTTPS que recebe cobrança paga, cancelada ou expirada.
- `php artisan inter:verificar` autentica no ambiente configurado. Certificado e chave ficam fora do git, em `INTER_CERTIFICADO` e `INTER_CHAVE`.

### Autenticação do painel (guard `painel`)

- Guard `painel` → provider `usuarios` → `App\Models\Usuario` (tabela `usuarios`). O login é feito pelo campo `login`, não por e-mail. O guard padrão `web` também aponta para `Usuario`, mas a sessão dele é separada.
- Login, logout e dashboard ficam em `Painel\AutenticacaoController`. Rotas protegidas entram no grupo `Route::prefix('painel')->middleware('auth:painel')` em `routes/web.php`.
- Dentro desse grupo, o `auth:painel` torna `painel` o guard padrão, então `Auth::user()` funciona ali (o template do painel e o `ConfiguracaoController` dependem disso). Fora dele, use `Auth::guard('painel')` explicitamente.
- Laravel 13: nunca registre middleware com `$this->middleware()` no construtor do controller. Use middleware na rota ou implemente `Illuminate\Routing\Controllers\HasMiddleware`.

### Permissões do painel

- `Usuario` ↔ `Grupo` (pivô `grupo_usuario`) ↔ `Acesso` (pivô `acesso_grupo`). Cada `Acesso.key` é uma permissão no formato `<modulo>_<visualizar|cadastrar|editar|deletar>`, por exemplo `usuario_visualizar`. `Usuario::acessos()` devolve as chaves do usuário e `hasPermissionTo()` verifica uma delas. Chaves novas vão no `PermissoesSeeder` (e no `AcessoSeeder`). Para liberá-las num banco existente, rode `php artisan db:seed` e faça logout/login (veja abaixo).
- Os controllers do painel estendem `Painel\PainelController`, cujo construtor carrega `$this->usuario` e `$this->listaPermissoes` da chave de sessão `permissoes`.
- Essa chave só é preenchida por `AutenticacaoController::principal()` (o dashboard em `/painel`) e fica na sessão até o logout. Por isso, mudanças nos grupos de um usuário só valem no próximo login dele.
- Nos controllers, use `checkPermissionOrFail('<chave>')` ou `hasPermission()`. As views verificam `in_array('<chave>', $listaPermissoes)` e o layout do painel monta o menu lateral com essa lista, então toda view do painel precisa receber `usuario` e `listaPermissoes` do controller (veja `UsuarioController`).

### Front-end

- **Site público** (`resources/views/site/`, layout `site.layout`): HTML com o CSS estático `public/assets/css/site.css`, carregado com `url()`. Não há etapa de build.
- **Painel** (`resources/views/template/painel.blade.php` e `resources/views/painel/`): Tailwind via CDN mais os arquivos estáticos escritos à mão `public/assets/css/app.css`, `public/assets/css/painel.css` e `public/assets/js/app.js`, carregados com `url()`. Edite-os diretamente. As páginas do painel fazem `@extends('template.painel')` e preenchem `@section('conteudo')`.
- O tema claro/escuro de cada usuário fica em `configuracao_usuario` (`ConfiguracaoUsuario`, criado sob demanda por `Usuario::getConfiguracao()`). A troca é feita via AJAX (`POST /painel/configuracao/toggle-tema`) e aplicada por `body[data-tema]` e pelas regras `.tema-escuro` do `painel.css`. Detalhes em `SISTEMA-TEMAS.md`.

### Datas e horas

Convenção: persistir em UTC e converter para o horário de Brasília só na interface.

- `config/app.php` mantém `timezone` em `UTC`; não mude isso. As conexões `mysql` e `mariadb` fixam `'timezone' => '+00:00'`, para que as colunas `TIMESTAMP` e o `NOW()` do banco também fiquem em UTC.
- O fuso de exibição é `config('app.display_timezone')` (`APP_DISPLAY_TIMEZONE`, padrão `America/Sao_Paulo`) e o locale é `pt_BR`. As mensagens padrão do framework (validação, auth, paginação e senhas) estão traduzidas em `lang/pt_BR/`, e o `lang/pt_BR.json` traduz as frases soltas, como o resumo de erros das respostas JSON e as páginas de erro. Nas mensagens de validação, o nome de cada campo vem de `attributes` em `lang/pt_BR/validation.php`; cadastre ali os campos de formulários novos. O `APP_FALLBACK_LOCALE` continua `en`, com os originais publicados em `lang/en/`: assim, uma chave que faltar em `lang/pt_BR/` (por exemplo, a de uma regra nova do framework) aparece em inglês, e não como a chave crua (`validation.required`).
- Toda conversão passa pelo `App\Services\DataHoraService`:
  - `agora()`: o momento atual em Brasília. Use para saber a data de "hoje".
  - `paraExibicao()` e `formatarDataHora()` (`d/m/Y H:i`): convertem um instante UTC para Brasília.
  - `paraUtc()`: converte a data/hora digitada num formulário (horário de Brasília) para UTC antes de gravar.
  - `formatarData()` (`d/m/Y`): formata sem converter o fuso.
- Nas views, use `@inject('dataHora', 'App\Services\DataHoraService')`. Não use `date()` nem `now()` sem fuso: eles devolvem o horário UTC, e das 21h à meia-noite de Brasília a data já é a do dia seguinte.
- Datas de calendário, como o vencimento de um boleto, devem ser colunas `date` e não passam por conversão de fuso (use `formatarData()`). Só instantes, como `created_at` ou a data/hora de um pagamento, são convertidos.

### Testes

- Os testes sempre rodam em SQLite em memória e com locale `pt_BR` (fixados no `phpunit.xml`), seja qual for o banco ou o `APP_LOCALE` configurado no `.env` local.
- Entre os models de domínio, o `Cliente` tem factory (`ClienteFactory`, com o estado `comPagador()`) e a `Assinatura` tem `mensal()`, `anual()` e `encerrada()`. A `UserFactory` aponta para um model `User` que não existe. Os testes de feature populam o banco com o `PermissoesSeeder` e autenticam com `actingAs($usuario, 'painel')->withSession(['permissoes' => $usuario->acessos()])`.
- Faça uma requisição por teste (use data providers para variações). O `PainelController` recebe a `Request` e as permissões no construtor, e o Laravel reaproveita a instância do controller entre requisições do mesmo teste, então uma segunda chamada enxerga a query string e as permissões da primeira.
- Os testes verificam textos renderizados em português (por exemplo, "Cobranças mensais em um só lugar"), então mudar textos da interface pode quebrá-los.
- Para testar datas, use `$this->travelTo()` com um horário UTC entre 00h e 03h, quando o dia em Brasília ainda é o anterior (veja `ConvencaoDataHoraTest`). Escolha datas longe da data real, para o teste não passar por coincidência.

### Arquivos relacionados

- `CLAUDE.md` e `.agent/skills/SKILL.md` têm a mesma seção de projeto (tudo antes do bloco do Laravel Boost). Mantenha os dois sincronizados.
- O bloco `<laravel-boost-guidelines>` abaixo é gerado pelo Laravel Boost e fica em inglês. O `php artisan boost:update` pode sobrescrever edições manuais nele.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.22
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/mcp (MCP) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- There is no frontend build step. The public site uses `public/assets/css/site.css` and the panel uses the Tailwind CDN plus `public/assets/`. Edit those files directly.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== laravel/v12 rules ===

## Laravel 13

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 13 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
</laravel-boost-guidelines>
