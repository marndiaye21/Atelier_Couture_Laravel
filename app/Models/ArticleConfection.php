<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ArticleConfection extends Article
{
    use HasFactory;

    protected $table = "articles_confection";

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'approvisionnements', 'article_id', 'provider_id');
    }

    public static function afterCreated(Article $article, array $providers, int $order): void
    {
        $article->providers()->attach($providers);
        Category::where("id", $article->category_id)->first()->update(["order" => $order]);
    }
    
    public static function afterUpdated(Article $article, array|null $providers, int|null $order): void
    {
        if ($providers) {
            $article->providers()->sync($providers);
        }
        if ($order) {
            Category::where("id", $article->category_id)->first()->update(["order" => $order]);
        }
    }
}
