<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryRessource;
use App\Models\Article;
use App\Models\ArticleConfection;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // $cat->load("articlesConfections");
        // return $cat;
        $perPage = $request->perPage ?? null;
        if ($perPage) {
            return new CategoryCollection(Category::with("articlesConfection")->paginate(intval($perPage)));
        }

        return $this->jsonResponse("", [], CategoryRessource::collection(Category::with("articlesConfection")->get()));
    }

    public function store(Request $request)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            "label" => "required|min:3",
            "type" => "required"
        ], [
            "label.required" => "Le nom de la catégorie est requis",
            "label.min" => "Le nom de la catégorie doit être au minimum 3 caractères",
            "type.required" => "Le type de catégorie est requis"
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse("", $validator->errors());
        }

        $existing = $this->handleExistingCategory($request);
        if ($existing) {
            return $existing;
        }

        return $this->jsonResponse("Catégorie enregistrer avec succès", [], [
            Category::create([
                "label" => $request->label,
                "type" => $request->type
            ])
        ]);
    }

    public function handleExistingCategory(Request $request) {
        /** @var Category $category */
        $category = Category::withTrashed()->byLabel($request->label)->first();
        if ($category) {
            if (!$category->trashed()) {
                return $this->jsonResponse("", ["Le nom de la catégorie existe déjà"]);
            }

            $category->restore();
            return $this->jsonResponse("Catégorie enregistrer avec succès", [], [$category]);
        }

        return null;
    }

    public function show(string $id)
    {
        /** @var Category $category */
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

        $dataToReturn = null;
        DB::transaction(function () use(&$dataToReturn, $request) {
            $dataToReturn = $this->jsonResponse("Les catégories ont étés supprimées avec succès", [], [Category::destroy($request->ids)]);
        });
        return $dataToReturn;
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
