<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Barème de réduction par tranche de quantité, défini par le fournisseur. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reductions_annonce', function (Blueprint $table) {
            $table->increments('id_reduction');
            $table->unsignedInteger('id_annonce');
            $table->unsignedInteger('quantite_min');
            $table->unsignedInteger('quantite_max');
            $table->decimal('pourcentage_reduction', 5, 2);
            $table->timestamps();

            $table->foreign('id_annonce')->references('id_annonce')->on('annonces')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reductions_annonce');
    }
};
