<?php

use App\Models\ArticleConfection;
use App\Models\ArticleVente;
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
        Schema::create('articles_confection_articles_vente', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ArticleConfection::class)->constrained("articles_confection");
            $table->foreignIdFor(ArticleVente::class)->constrained("articles_vente");
            $table->integer("article_confection_quantity");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles_confection_articles_vente');
    }
};
