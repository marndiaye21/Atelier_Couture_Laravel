<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ArticleVente extends Article
{
    use HasFactory;

    protected $table = "articles_vente";

    public function articlesConfection() : BelongsToMany
    {
        return $this
                ->belongsToMany(ArticleConfection::class, "articles_confection_articles_vente")
                ->withPivot('article_confection_quantity');
    }

    public static function afterCreated(ArticleVente $article, array &$articleConfections) : void
    {
        $keys = array_keys($articleConfections);
        array_map(function ($key) use (&$articleConfections) {
            $articleConfections[$key] = ["article_confection_quantity" => $articleConfections[$key]];
        }, $keys);
        $article->articlesConfection()->attach($articleConfections);

        /** @var Category $category */
        $category = Category::where("id", $article->category_id)->first();
        $category->update(["order" => $category->order + 1]);
    }

    public static function afterUpdated(ArticleVente $article, array|null &$articleConfections): void
    {
        if ($articleConfections) {
            $keys = array_keys($articleConfections);
            array_map(function ($key) use (&$articleConfections) {
                $articleConfections[$key] = ["article_confection_quantity" => $articleConfections[$key]];
            }, $keys);
            $article->articlesConfection()->sync($articleConfections);
        }
    }
}
