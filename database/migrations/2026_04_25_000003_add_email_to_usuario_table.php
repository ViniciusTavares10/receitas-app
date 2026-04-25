<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuario') || Schema::hasColumn('usuario', 'email')) {
            return;
        }

        Schema::table('usuario', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('usuario') || ! Schema::hasColumn('usuario', 'email')) {
            return;
        }

        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
