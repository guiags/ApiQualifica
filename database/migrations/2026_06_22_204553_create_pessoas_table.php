<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pessoas_cadastradas', function (Blueprint $table) {
            $table->id();
            $table->string('cpf', 14)->unique();
            $table->string('rg', 20);
            $table->string('nome');
            $table->string('nome_mae');
            $table->text('endereco');
            $table->string('estado_civil', 30);
            $table->string('profissao', 100);
            $table->string('escolaridade', 100);
            $table->string('email', 150)->nullable();
            $table->string('telefone', 20);
            $table->string('contato', 150);
            $table->boolean('possui_passagem_criminal')->default(false);
            $table->string('foto_url');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Relacionamento com o usuário que realizou o cadastro
            $table->foreignId('criado_por')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoas_cadastradas');
    }
};