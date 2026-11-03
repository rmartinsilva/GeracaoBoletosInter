<?php

namespace Tests\Feature;

use App\Models\Acesso;
use App\Models\Grupo;
use App\Models\Usuario;
use Database\Seeders\PermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PermissoesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_recebe_as_permissoes_de_clientes(): void
    {
        $this->seed(PermissoesSeeder::class);

        $admin = Usuario::query()->where('login', 'admin')->firstOrFail();

        foreach (['cliente_visualizar', 'cliente_cadastrar', 'cliente_editar', 'cliente_deletar'] as $chave) {
            $this->assertTrue($admin->hasPermissionTo($chave), "Admin sem a permissão {$chave}.");
        }
    }

    public function test_rodar_de_novo_nao_duplica_registros_nem_troca_a_senha_do_admin(): void
    {
        $this->seed(PermissoesSeeder::class);

        $admin = Usuario::query()->where('login', 'admin')->firstOrFail();
        $admin->update(['password' => 'senha-trocada']);

        $this->seed(PermissoesSeeder::class);

        $this->assertSame(1, Grupo::query()->where('descricao', 'Administrador')->count());
        $this->assertSame(1, Usuario::query()->where('login', 'admin')->count());
        $this->assertSame(8, Acesso::query()->count());
        $this->assertCount(8, $admin->fresh()->acessos());
        $this->assertTrue(Hash::check('senha-trocada', $admin->fresh()->password));
    }

    public function test_banco_que_so_tinha_usuarios_ganha_as_permissoes_de_clientes(): void
    {
        $this->seed(PermissoesSeeder::class);
        Acesso::query()->where('key', 'like', 'cliente_%')->delete();

        $admin = Usuario::query()->where('login', 'admin')->firstOrFail();
        $this->assertFalse($admin->hasPermissionTo('cliente_visualizar'));

        $this->seed(PermissoesSeeder::class);

        $this->assertTrue($admin->fresh()->hasPermissionTo('cliente_visualizar'));
        $this->assertTrue($admin->fresh()->hasPermissionTo('usuario_visualizar'));
    }
}
