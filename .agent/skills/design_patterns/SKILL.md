# Padrões de Implementação

## Fluxo de Dados em CRUDs

```
Request → Controller → Service → Repository → Database
                ↓
             Response
```

1. **Controller**: Recebe a requisição, delega ao Service
2. **Service**: Concentra a lógica de negócios
3. **Repository**: Centraliza o acesso ao banco de dados

## Controller Pattern

### Estrutura Básica

```php
class UsuarioController extends Controller
{
    public function __construct(
        protected UsuarioService $service
    ) {}

    // Métodos CRUD padrão:
    // - index()    -> Listagem paginada
    // - store()    -> Criação
    // - show()     -> Exibição única
    // - update()   -> Atualização
    // - destroy()  -> Exclusão
}
```

### Method: index() - Listagem Paginada

```php
public function index(Request $request)
{
    $items = $this->service->paginate(
        page: $request->get('page', 1),
        totalPerPage: $request->get('per_page', 15),
        filter: $request->filter,
    );

    return ItemResource::collection($items->items())
        ->additional([
            'meta' => ApiAdapter::pagination($items),
        ]);
}
```

**Características:**

- Usa `Request` genérico (não precisa de FormRequest)
- Parâmetros: `page`, `per_page`, `filter`
- Retorna `ResourceCollection` com metadados de paginação

### Method: store() - Criação

```php
public function store(StoreItemRequest $request)
{
    try {
        $item = $this->service->new(CreateItemDTO::makeFromRequest($request));
        $objRetorno = new ItemObjectResource($item);
        return response()->json($objRetorno->toObject(), Response::HTTP_CREATED);
    } catch (UsinaWeb_Exception $ex) {
        return response()->json([
            "error" => $ex->getMensagem()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    } catch (Exception $ex) {
        return response()->json([
            "error" => "Erro ao criar item! " . $ex->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
```

**Características:**

- Usa `StoreItemRequest` para validação
- Converte Request → DTO via `makeFromRequest()`
- Retorna HTTP 201 em sucesso
- Tratamento de exceções customizadas e genéricas

### Method: show() - Exibição Única

```php
public function show(string $id)
{
    if (!$item = $this->service->findOne($id)) {
        return response()->json(["error" => "Item não encontrado!"], Response::HTTP_NOT_FOUND);
    }

    $objRetorno = new ItemObjectResource($item);
    return $objRetorno->toObject();
}
```

**Características:**

- Verifica existência antes de retornar
- Retorna HTTP 404 se não encontrado
- Usa `ObjectResource` para formatar resposta

### Method: update() - Atualização

```php
public function update(UpdateItemRequest $request, string $id)
{
    // 1. Verifica se existe
    if (!$item = $this->service->findOne($id)) {
        return response()->json([
            "error" => "Item não encontrado!"
        ], Response::HTTP_NOT_FOUND);
    }

    try {
        // 2. Cria o DTO e atualiza
        $dto = UpdateItemDTO::makeFromRequest($request, $id);
        $itemAtualizado = $this->service->update($dto);

        // 3. Retorna o item atualizado
        $objRetorno = new ItemObjectResource($itemAtualizado);
        return response()->json($objRetorno->toObject(), Response::HTTP_OK);
    } catch (UsinaWeb_Exception $ex) {
        return response()->json([
            "error" => $ex->getMensagem()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    } catch (Exception $ex) {
        return response()->json([
            "error" => "Erro ao atualizar item! " . $ex->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
```

**Características:**

- Verifica existência primeiro
- Usa `UpdateItemRequest` para validação
- DTO recebe o `$id` como parâmetro extra
- Retorna HTTP 200 em sucesso

### Method: destroy() - Exclusão

```php
public function destroy(string $id)
{
    if (!$this->service->findOne($id)) {
        return response()->json(["error" => "Item não encontrado!"], Response::HTTP_NOT_FOUND);
    }

    try {
        $this->service->delete($id);
        return response()->json([], Response::HTTP_NO_CONTENT);
    } catch (UsinaWeb_Exception $ex) {
        return response()->json([
            "error" => $ex->getMensagem()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    } catch (Exception $ex) {
        return response()->json([
            "error" => "Erro ao deletar item! " . $ex->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
```

**Características:**

- Verifica existência antes de deletar
- Retorna HTTP 204 (No Content) em sucesso
- Retorna array vazio no body

## Request Pattern

### Store Request (Criação)

```php
class StoreItemRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('items', 'email')
            ],
        ];
    }
}
```

**Características:**

- Herda de `BaseRequest`
- Todas as regras são `required`
- Usa `Rule::unique()` para validações de unicidade

### Update Request (Atualização)

```php
class UpdateItemRequest extends BaseRequest
{
    public function rules(): array
    {
        $itemId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('items', 'email')->ignore($itemId)
            ],
        ];
    }
}
```

**Características:**

- Usa `sometimes` para permitir atualização parcial
- Obtém ID via `$this->route('id')`
- Usa `Rule::unique()->ignore($itemId)` para ignorar o próprio registro

## DTO Pattern

### Create DTO

```php
class CreateItemDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            $request->name,
            $request->email,
            $request->password
        );
    }
}
```

**Características:**

- Usa constructor property promotion
- Tipagem forte em todas as propriedades
- Factory method estático `makeFromRequest()`

### Update DTO

```php
class UpdateItemDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public ?string $password = null
    ) {}

    public static function makeFromRequest(Request $request, string $id): self
    {
        return new self(
            $id,
            $request->name,
            $request->email,
            $request->password ?? null
        );
    }
}
```

**Características:**

- Sempre inclui o `$id` como primeiro parâmetro
- Campos opcionais usam `?type` e valor default `null`
- Factory method recebe `$id` como segundo parâmetro

## Service Pattern

```php
class ItemService
{
    public function __construct(
        protected ItemRepositoryInterface $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(
            page: $page,
            totalPerPage: $totalPerPage,
            filter: $filter,
        );
    }

    public function findOne(string $id): ?stdClass
    {
        return $this->repository->findOne($id);
    }

    public function new(CreateItemDTO $dto): stdClass
    {
        // Lógica de negócio adicional pode ser adicionada aqui
        return $this->repository->new($dto);
    }

    public function update(UpdateItemDTO $dto): ?stdClass
    {
        // Lógica de negócio adicional pode ser adicionada aqui
        return $this->repository->update($dto);
    }

    public function delete(string $id): void
    {
        // Lógica de negócio adicional pode ser adicionada aqui
        $this->repository->delete($id);
    }
}
```

**Características:**

- Injeta a **Interface** do Repository, não a implementação
- Delega operações de dados para o Repository
- Local ideal para lógica de negócios complexa
- Mantém as mesmas assinaturas de métodos do Repository

## Repository Pattern

### Interface

```php
interface ItemRepositoryInterface
{
    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface;
    public function getAll(string $filter = null);
    public function findOne(string $id): ?stdClass;
    public function delete(string $id): void;
    public function new(CreateItemDTO $dto): stdClass;
    public function update(UpdateItemDTO $dto): ?stdClass;
}
```

### Implementação

```php
class ItemRepository implements ItemRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        try {
            $result = $this->model
                ->when($filter, function ($query) use ($filter) {
                    $query->where('name', 'like', "%{$filter}%");
                    $query->orWhere('email', 'like', "%{$filter}%");
                })
                ->paginate($totalPerPage, ['*'], 'page', $page);

            return new PaginationPresenter($result);
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function findOne(string $id): ?stdClass
    {
        try {
            $item = $this->model->find($id);
            if (!$item) {
                return null;
            }
            return (object) $item->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function new(CreateItemDTO $dto): stdClass
    {
        try {
            $data = (array) $dto;
            // Lógica específica (ex: hash de senha) aqui
            $item = $this->model->create($data);
            return (object) $item->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function update(UpdateItemDTO $dto): ?stdClass
    {
        try {
            if (!$item = $this->model->find($dto->id)) {
                return null;
            }

            $data = (array) $dto;
            // Lógica específica (ex: remover senha vazia) aqui
            $item->update($data);
            return (object) $item->toArray();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }

    public function delete(string $id): void
    {
        try {
            $this->model->findOrFail($id)->delete();
        } catch (Exception $ex) {
            throw new UsinaWeb_Exception($ex);
        }
    }
}
```

**Características:**

- Implementa a Interface
- Injeta o Model com alias `Model`
- Todo método tem try-catch com `UsinaWeb_Exception`
- Converte Eloquent Models para `stdClass` via `toArray()`
- Usa `when()` para filtros condicionais
- Retorna `PaginationPresenter` para paginação

## Resource Pattern

### Collection Resource (para listas)

```php
class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'email' => $this->email ?? null,
        ];
    }
}
```

**Uso:**

```php
return ItemResource::collection($items->items())
    ->additional(['meta' => ApiAdapter::pagination($items)]);
```

### Object Resource (para objetos únicos)

```php
class ItemObjectResource
{
    public stdClass $objeto;

    public function __construct(stdClass $objeto) {
        $this->objeto = $objeto;
    }

    public function toObject(): array
    {
        return [
            'id' => $this->objeto->id ?? null,
            'name' => $this->objeto->name ?? null,
            'email' => $this->objeto->email ?? null,
        ];
    }
}
```

**Uso:**

```php
$objRetorno = new ItemObjectResource($item);
return response()->json($objRetorno->toObject(), Response::HTTP_OK);
```

**Características:**

- `Resource` estende `JsonResource` (para listas)
- `ObjectResource` é uma classe simples (para objetos únicos)
- Ambos usam `?? null` para segurança
- ObjectResource recebe `stdClass` no construtor
