<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticlePutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "label" => "sometimes|required|min:3",
            "price" => "sometimes|required|min:0",
            "stock" => "sometimes|required|min:0",
            "category_id" => "sometimes|required|exists:categories,id",
            "providers" => "sometimes|required",
            "reference" => "sometimes|required|unique:articles_confection",
            "order" => "sometimes|required",
            "photo" => "image|max:2000"
        ];
    }

    public function messages() : array
    {
        return [
            "label.required" => "Le nom de l'article est requis",
            "label.min" => "Le nom de l'article doit être au minimum 3 caractères",
            "price.required" => "Le prix de l'article est requis",
            "price.min" => "Le prix de l'article doit être positif",
            "stock.required" => "La quantité de stock de l'article est requis",
            "stock.min" => "La quantité de stock de l'article doit être positif",
            "category_id.required" => "Il est nécéssaire de préciser la catégorie à laquelle appartient cet article",
            "category_id.exists" => "La catégorie indiqué n'existe pas",
            "providers.required" => "Il est nécéssaire de préciser le ou les fournisseurs qui fournit cet article",
            "reference.required" => "La référence de l'article est requis",
            "reference.unique" => "La référence indiqué existe déjà",
            "order.required" => "L'ordre de la catégorie est requis",
            "photo.image" => "La photo doit être une image valide",
            "photo.max" => "La taille de la photo ne doit pas dépasser 2 MB"
        ];
    }
}
