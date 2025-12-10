<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 32)->unique();
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();

        DB::table('roles')->insert([
            ['nombre' => 'prosumer'],
            ['nombre' => 'administrador'],
            ['nombre' => 'master']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('roles');
        Schema::enableForeignKeyConstraints();

    }
};
