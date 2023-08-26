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

    public function scopeByLabel(Builder $builder, string $label): Builder
    {
        return $builder->where("label", $label);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
