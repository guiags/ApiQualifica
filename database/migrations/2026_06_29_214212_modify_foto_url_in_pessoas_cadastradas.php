<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {
            Schema::table('pessoas_cadastradas', function (Blueprint $table) {
                // Altera a coluna para aceitar valores nulos
                $table->string('foto_url')->nullable()->change();
            });
        }

        public function down()
        {
            Schema::table('pessoas_cadastradas', function (Blueprint $table) {
                // Caso precise reverter
                $table->string('foto_url')->nullable(false)->change();
            });
        }
};
