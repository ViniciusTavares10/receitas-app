<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuario')) {
            return;
        }

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 100);
            $table->string('email', 255)->unique();
            $table->string('login', 50)->unique();
            $table->string('senha', 100);
            $table->boolean('situacao')->default(true);
        });

        DB::table('usuario')->insert([
            'nome' => 'teste',
            'email' => 'teste@gmail.com',
            'login' => 'teste',
            'senha' => bcrypt('123'),
            'situacao' => true,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
