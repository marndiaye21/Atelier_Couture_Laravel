<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->perPage ?? null;
        if ($perPage) {
            return $this->jsonResponse("", [], Category::paginate(intval($perPage)));
        }

        return $this->jsonResponse("", [], Category::all());
    }

    public function store(Request $request)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            "label" => "required|min:3",
            "order" => "required"
        ], [
            "label.required" => "Le nom de la catégorie est requis",
            "label.min" => "Le nom de la catégorie doit être au minimum 3 caractères",
            "order.required" => "L'ordre de la catégorie est requis"
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse("", $validator->errors());
        }

        /** @var Category $category */
        $category = Category::withTrashed()->byLabel($request->label)->first();
        if ($category) {
            if (!$category->trashed()) {
                return $this->jsonResponse("", ["Le nom de la catégorie existe déjà"]);
            }

            $category->restore();
            return $this->jsonResponse("Catégorie enregistrer avec succès", [], [$category]);
        }

        return $this->jsonResponse("Catégorie enregistrer avec succès", [], [
            Category::create([
                "label" => $request->label
            ])
        ]);
    }

    public function show(string $id)
    {
        $category = Category::where("id", $id)->with("articles")->first();

        if (!$category) {
            $this->jsonResponse("", ["La catégorie que vous essayer de chercher n'existe pas"]);
        }

        return $this->jsonResponse("Catégorie récupérée avec succès", [], [$category]);
    }

    public function update(Request $request, string $id)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            "label" => "sometimes|required|min:3|unique:categories"
        ], [
            "label.required" => "Le nom de la catégorie est requis",
            "label.min" => "Le nom de la catégorie doit être au minimum 3 caractères",
            "label.unique" => "La catégorie que vous avez saisie existe déjà"
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse("", $validator->errors());
        }

        /** @var Category $category */
        $category = Category::where("id", $id)->first();
        $category->update($request->only("label"));
        return $this->jsonResponse("Catégorie modifié avec succès", [], [$category]);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "ids" => "required|array"
        ], [
            "ids.required" => "Les idenfiants des catégories à supprimées sont requis",
            "ids.array" => "Les idenfiants des catégories à supprimées doivent être sous forme de tableau"
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse("", $validator->errors());
        }

        return $this->jsonResponse("Les catégories ont étés supprimées avec succès", [], [Category::destroy($request->ids)]);
    }

    public function search(string $label)
    {
        if (!$label) {
            return $this->jsonResponse("", ["Le nom de la catégorie est requis !"]);
        }

        $category = Category::byLabel($label)->first();
        if (!$category) {
            return $this->jsonResponse("", ["La catégorie que vous cherchez n'existe pas"]);
        }

        return $this->jsonResponse("Catégorie trouvée", [], [$category]);
    }
}
