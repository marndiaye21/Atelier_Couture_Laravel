<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleVentePutRequest extends FormRequest
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
            "label" => "sometimes|required|unique:articles_vente|min:3",
            "sales_price" => "sometimes|required|min:0",
            "promo" => "sometimes|min:0",
            "stock" => "sometimes|required|min:0",
            "photo" => "sometimes|required|image|max:2000",
            "reference" => "sometimes|required",
            "manufacturing_cost" => "sometimes|required|min:0",
            "marge" => "sometimes|required|min:0",
            "articles_confection" => "sometimes|required|array",
            "category_id" => "sometimes|required|exists:categories,id",
        ];
    }
}
