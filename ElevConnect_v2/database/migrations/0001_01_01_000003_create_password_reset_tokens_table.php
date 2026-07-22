<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table standard de Laravel pour les jetons de réinitialisation de mot de
 * passe (Password::sendResetLink() / Password::reset()). Absente du
 * squelette livré jusqu'ici — nécessaire pour la fonctionnalité "mot de
 * passe oublié" (exigence explicite du mémoire, chap. 2 et plan de test du
 * chap. 5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
