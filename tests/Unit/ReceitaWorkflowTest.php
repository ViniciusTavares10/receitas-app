<?php

namespace Tests\Unit;

use App\Mail\ReceitaNotificationMail;
use App\Models\Receita;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReceitaWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_index_displays_all_records_without_filters(): void
    {
        Receita::factory()->create(['nome' => 'Bolo']);
        Receita::factory()->create(['nome' => 'Torta']);

        $response = $this->get(route('receitas.index'));

        $response->assertOk()
            ->assertSee('Bolo')
            ->assertSee('Torta');
    }

    public function test_index_filters_records_by_status(): void
    {
        Receita::factory()->create(['nome' => 'Receita ativa', 'status' => 'ATIVO']);
        Receita::factory()->create(['nome' => 'Receita inativa', 'status' => 'INATIVO']);

        $response = $this->get(route('receitas.index', ['status' => 'ATIVO']));

        $response->assertOk()
            ->assertSee('Receita ativa')
            ->assertDontSee('Receita inativa');
    }

    public function test_index_filters_records_by_date_interval(): void
    {
        Receita::factory()->create(['nome' => 'Fora do periodo', 'data_registro' => '2026-04-01']);
        Receita::factory()->create(['nome' => 'Dentro do periodo', 'data_registro' => '2026-04-18']);

        $response = $this->get(route('receitas.index', [
            'data_inicial' => '2026-04-10',
            'data_final' => '2026-04-20',
        ]));

        $response->assertOk()
            ->assertSee('Dentro do periodo')
            ->assertDontSee('Fora do periodo');
    }

    public function test_store_creates_record_with_status(): void
    {
        $usuario = Usuario::factory()->create();
        $this->withSession(['usuario' => $usuario]);

        $response = $this->post(route('receitas.store'), [
            'nome' => 'Pudim',
            'descricao' => 'Pudim de leite',
            'data_registro' => '2026-04-25',
            'custo' => '35.60',
            'tipo_receita' => 'doce',
            'status' => 'ATIVO',
        ]);

        $response->assertRedirect(route('receitas.index'));

        $this->assertDatabaseHas('receita', [
            'nome' => 'Pudim',
            'status' => 'ATIVO',
        ]);
    }

    public function test_store_validates_required_status(): void
    {
        $usuario = Usuario::factory()->create();
        $this->withSession(['usuario' => $usuario]);

        $response = $this->from(route('receitas.index'))->post(route('receitas.store'), [
            'nome' => 'Pudim',
            'descricao' => 'Pudim de leite',
            'data_registro' => '2026-04-25',
            'custo' => '35.60',
            'tipo_receita' => 'doce',
        ]);

        $response->assertRedirect(route('receitas.index'));
        $response->assertSessionHasErrors('status');
    }

    public function test_store_sends_notification_email(): void
    {
        Mail::fake();
        $usuario = Usuario::factory()->create(['email' => 'cliente@example.com']);

        $this->withSession(['usuario' => $usuario])->post(route('receitas.store'), [
            'nome' => 'Brigadeiro',
            'descricao' => 'Venda avulsa',
            'data_registro' => '2026-04-25',
            'custo' => '12.00',
            'tipo_receita' => 'doce',
            'status' => 'ATIVO',
        ]);

        Mail::assertSent(ReceitaNotificationMail::class, function (ReceitaNotificationMail $mail) {
            return $mail->action === 'created'
                && $mail->hasTo('cliente@example.com')
                && $mail->receita->nome === 'Brigadeiro';
        });
    }

    public function test_update_changes_record_and_status(): void
    {
        $usuario = Usuario::factory()->create();
        $this->withSession(['usuario' => $usuario]);

        $receita = Receita::factory()->create([
            'nome' => 'Cookie',
            'status' => 'INATIVO',
        ]);

        $response = $this->put(route('receitas.update', $receita->id), [
            'nome' => 'Cookie premium',
            'descricao' => 'Versao atualizada',
            'data_registro' => '2026-04-25',
            'custo' => '45.00',
            'tipo_receita' => 'doce',
            'status' => 'ATIVO',
        ]);

        $response->assertRedirect(route('receitas.index'));

        $this->assertDatabaseHas('receita', [
            'id' => $receita->id,
            'nome' => 'Cookie premium',
            'status' => 'ATIVO',
        ]);
    }

    public function test_destroy_sends_notification_email_to_logged_user(): void
    {
        Mail::fake();
        $usuario = Usuario::factory()->create(['email' => 'cliente@example.com']);
        $receita = Receita::factory()->create();

        $this->withSession(['usuario' => $usuario])->delete(route('receitas.destroy', $receita->id));

        Mail::assertSent(ReceitaNotificationMail::class, function (ReceitaNotificationMail $mail) use ($receita) {
            return $mail->action === 'deleted'
                && $mail->hasTo('cliente@example.com')
                && $mail->receita->id === $receita->id;
        });
    }

    public function test_export_pdf_returns_pdf_response_with_filtered_content(): void
    {
        Receita::factory()->create(['nome' => 'Relatorio A', 'status' => 'ATIVO']);
        Receita::factory()->create(['nome' => 'Relatorio B', 'status' => 'INATIVO']);

        $response = $this->get(route('receitas.export-pdf', ['status' => 'ATIVO']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertSee('%PDF', false);
        $response->assertSee('Relatorio A', false);
        $response->assertDontSee('Relatorio B', false);
    }

    public function test_destroy_removes_record(): void
    {
        $usuario = Usuario::factory()->create();
        $this->withSession(['usuario' => $usuario]);
        $receita = Receita::factory()->create();

        $response = $this->delete(route('receitas.destroy', $receita->id));

        $response->assertRedirect(route('receitas.index'));
        $this->assertDatabaseMissing('receita', ['id' => $receita->id]);
    }
}
