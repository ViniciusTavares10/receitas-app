<?php

namespace Tests\Unit;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_register_creates_user_with_email(): void
    {
        $response = $this->from('/')->post(route('auth.register'), [
            'nome' => 'Cliente Teste',
            'email' => 'cliente.teste@example.com',
            'login' => 'cliente_teste',
            'senha' => '123456',
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('usuario', [
            'nome' => 'Cliente Teste',
            'email' => 'cliente.teste@example.com',
            'login' => 'cliente_teste',
        ]);
    }

    public function test_login_stores_user_with_email_in_session(): void
    {
        $usuario = Usuario::factory()->create([
            'email' => 'logado@example.com',
            'login' => 'cliente_login',
            'senha' => '123456',
        ]);

        $response = $this->post('/login', [
            'login' => 'cliente_login',
            'senha' => '123456',
        ]);

        $response->assertRedirect('/receitas');
        $response->assertSessionHas('usuario');

        $this->assertSame(
            $usuario->email,
            data_get(session('usuario'), 'email')
        );
    }
}
