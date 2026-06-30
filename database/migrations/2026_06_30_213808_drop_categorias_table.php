<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('categorias');
    }

    public function down(): void
    {
        Schema::create('categorias', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });
    }
};