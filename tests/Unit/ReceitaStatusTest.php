<?php

namespace Tests\Unit;

use App\Models\Receita;
use PHPUnit\Framework\TestCase;

class ReceitaStatusTest extends TestCase
{
    public function test_receita_define_status_ativo_e_inativo(): void
    {
        $this->assertSame('ATIVO', Receita::STATUS_ATIVO);
        $this->assertSame('INATIVO', Receita::STATUS_INATIVO);
    }
}
