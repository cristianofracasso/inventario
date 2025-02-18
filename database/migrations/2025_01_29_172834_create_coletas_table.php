<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coletas', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('serial')->nullable();
            $table->string('codigo_palet');
            $table->string('contagem');
            $table->string('grupo');
            $table->string('custos');
            $table->enum('status', ['em_andamento', 'finalizada'])->default('em_andamento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coletas');
    }
};
