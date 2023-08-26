<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleVenteRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "label" => $this->label,
            "sales_price" => $this->sales_price,
            "promo" => $this->promo,
            "stock" => $this->stock,
            "photo" => $this->photo,
            "reference" => $this->reference,
            "manufacturing_cost" => $this->manufacturing_cost,
            "marge" => $this->marge,
            "category" => new CategoryRessource($this->category),
            "articles_confection" => ArticleRessource::collection($this->articlesConfection),
        ];
    }
}
