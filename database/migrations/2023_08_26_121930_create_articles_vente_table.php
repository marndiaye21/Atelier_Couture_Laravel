<?php

use App\Models\Category;
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
        Schema::create('articles_vente', function (Blueprint $table) {
            $table->id();
            $table->string("label")->unique();
            $table->float("sales_price");
            $table->float("promo")->nullable()->default(0);
            $table->integer("stock")->default(0);
            $table->string("photo");
            $table->string("reference");
            $table->float("manufacturing_cost");
            $table->float("marge");
            $table->foreignIdFor(Category::class)->constrained();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles_vente');
    }
};
