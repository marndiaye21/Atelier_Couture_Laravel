<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleConfection_ArticleVente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table= "articles_confection_articles_vente";
}
