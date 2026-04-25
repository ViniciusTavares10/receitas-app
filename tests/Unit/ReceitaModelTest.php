<?php

namespace Tests\Unit;

use App\Models\Receita;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReceitaModelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_status_options_returns_expected_values(): void
    {
        $this->assertSame([
            'ATIVO' => 'ATIVO',
            'INATIVO' => 'INATIVO',
        ], Receita::statusOptions());
    }

    public function test_fillable_contains_status_field(): void
    {
        $receita = new Receita();

        $this->assertContains('status', $receita->getFillable());
    }

    public function test_casts_date_and_cost_fields(): void
    {
        $receita = Receita::factory()->create([
            'data_registro' => '2026-04-20',
            'custo' => 145.9,
        ])->fresh();

        $this->assertSame('2026-04-20', $receita->data_registro->format('Y-m-d'));
        $this->assertSame('145.90', $receita->custo);
    }

    public function test_scope_filter_by_status(): void
    {
        Receita::factory()->create(['nome' => 'Alpha', 'status' => 'ATIVO']);
        Receita::factory()->create(['nome' => 'Beta', 'status' => 'INATIVO']);

        $result = Receita::query()
            ->whereIn('nome', ['Alpha', 'Beta'])
            ->filter(['status' => 'ATIVO'])
            ->pluck('nome')
            ->all();

        $this->assertSame(['Alpha'], $result);
    }

    public function test_scope_filter_by_start_date(): void
    {
        Receita::factory()->create(['nome' => 'Antiga', 'data_registro' => '2026-04-10']);
        Receita::factory()->create(['nome' => 'Nova', 'data_registro' => '2026-04-20']);

        $result = Receita::query()->filter(['data_inicial' => '2026-04-15'])->pluck('nome')->all();

        $this->assertSame(['Nova'], $result);
    }

    public function test_scope_filter_by_end_date(): void
    {
        Receita::factory()->create(['nome' => 'Antiga', 'data_registro' => '2026-04-10']);
        Receita::factory()->create(['nome' => 'Nova', 'data_registro' => '2026-04-20']);

        $result = Receita::query()
            ->whereIn('nome', ['Antiga', 'Nova'])
            ->filter(['data_final' => '2026-04-15'])
            ->pluck('nome')
            ->all();

        $this->assertSame(['Antiga'], $result);
    }

    public function test_scope_filter_by_date_range_and_status(): void
    {
        Receita::factory()->create([
            'nome' => 'Dentro do filtro',
            'data_registro' => '2026-04-20',
            'status' => 'ATIVO',
        ]);
        Receita::factory()->create([
            'nome' => 'Status diferente',
            'data_registro' => '2026-04-20',
            'status' => 'INATIVO',
        ]);
        Receita::factory()->create([
            'nome' => 'Data fora',
            'data_registro' => '2026-04-02',
            'status' => 'ATIVO',
        ]);

        $result = Receita::query()->filter([
            'data_inicial' => '2026-04-15',
            'data_final' => '2026-04-25',
            'status' => 'ATIVO',
        ])->pluck('nome')->all();

        $this->assertSame(['Dentro do filtro'], $result);
    }

    public function test_scope_filter_ignores_empty_values(): void
    {
        Receita::factory()->create(['nome' => 'Filtro vazio A']);
        Receita::factory()->create(['nome' => 'Filtro vazio B']);

        $result = Receita::query()
            ->whereIn('nome', ['Filtro vazio A', 'Filtro vazio B'])
            ->filter([
            'data_inicial' => null,
            'data_final' => '',
            'status' => null,
        ])->count();

        $this->assertSame(2, $result);
    }

    public function test_database_default_status_is_ativo(): void
    {
        DB::table('receita')->insert([
            'nome' => 'Sem status informado',
            'descricao' => 'Teste',
            'data_registro' => '2026-04-25',
            'custo' => 10,
            'tipo_receita' => 'doce',
        ]);

        $receita = Receita::where('nome', 'Sem status informado')->first();

        $this->assertSame('ATIVO', $receita->status);
    }

    public function test_mass_assignment_accepts_status_field(): void
    {
        $receita = Receita::create([
            'nome' => 'Nova Receita',
            'descricao' => 'Descricao',
            'data_registro' => '2026-04-25',
            'custo' => 20.5,
            'tipo_receita' => 'salgada',
            'status' => 'INATIVO',
        ]);

        $this->assertSame('INATIVO', $receita->status);
    }
}
