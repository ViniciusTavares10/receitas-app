<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('receita')) {
            return;
        }

        Schema::create('receita', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 100);
            $table->text('descricao')->nullable();
            $table->date('data_registro')->default(DB::raw('CURRENT_DATE'));
            $table->decimal('custo', 10, 2);
            $table->string('tipo_receita', 10);
            $table->string('status', 20)->default('ATIVO');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receita');
    }
};
