<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('receita') || ! Schema::hasColumn('receita', 'status')) {
            return;
        }

        DB::table('receita')->where('status', 'pendente')->update(['status' => 'ATIVO']);
        DB::table('receita')->whereIn('status', ['recebido', 'cancelado'])->update(['status' => 'INATIVO']);
        DB::statement("ALTER TABLE receita ALTER COLUMN status SET DEFAULT 'ATIVO'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('receita') || ! Schema::hasColumn('receita', 'status')) {
            return;
        }

        DB::table('receita')->where('status', 'ATIVO')->update(['status' => 'pendente']);
        DB::table('receita')->where('status', 'INATIVO')->update(['status' => 'recebido']);
        DB::statement("ALTER TABLE receita ALTER COLUMN status SET DEFAULT 'pendente'");
    }
};
