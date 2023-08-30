<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleRessource extends JsonResource
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
            "price" => $this->price,
            "stock" => $this->stock,
            "reference" => $this->reference,
            "photo" => $this->photo,
            "category" => new CategoryRessource($this->category),
            "providers" => ProviderRessource::collection($this->providers),
            "pivot" => $this->whenPivotLoaded('articles_confection_articles_vente', function () {
                return [
                    "article_confection_quantity" => $this->pivot->article_confection_quantity
                ];
            }),
        ];
    }
}
