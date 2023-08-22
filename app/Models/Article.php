<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'approvisionnements', 'article_id', 'provider_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeByLabel(Builder $builder, string $label): Builder
    {
        return $builder->where("label", $label);
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
