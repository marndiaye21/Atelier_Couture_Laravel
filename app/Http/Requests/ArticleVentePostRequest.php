<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleVentePostRequest extends FormRequest
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
            "label" => "required|unique:articles_vente|min:3",
            "sales_price" => "required|min:0",
            "promo" => "sometimes|min:0",
            "stock" => "sometimes|required|min:0",
            "photo" => "required|image|max:2000",
            "reference" => "required",
            "manufacturing_cost" => "required|min:0",
            "marge" => "required|min:0",
            "articles_confection" => "required|array",
            "category_id" => "required|exists:categories,id",
        ];
    }
}
