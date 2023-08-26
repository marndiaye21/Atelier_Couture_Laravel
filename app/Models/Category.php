<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function scopeByLabel(Builder $builder, string $label) : Builder
    {
        return $builder->where("label", $label);
    }

    public function scopeByType(Builder $builder, string $type) : Builder
    {
        return $builder->where("type", $type);
    }

    public function articlesConfection() : HasMany
    {
        return $this->hasMany(ArticleConfection::class, "category_id");
    }

    public static function booted()
    {
        static::deleted(function (Category $category) {
            $category->articlesConfection()->delete();
        });
    }
}
