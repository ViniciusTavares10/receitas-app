<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('receita') || Schema::hasColumn('receita', 'status')) {
            return;
        }

        Schema::table('receita', function (Blueprint $table) {
            $table->string('status', 20)->default('ATIVO')->after('tipo_receita');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('receita') || ! Schema::hasColumn('receita', 'status')) {
            return;
        }

        Schema::table('receita', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
