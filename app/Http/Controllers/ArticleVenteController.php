<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleVentePostRequest;
use App\Http\Resources\ArticleRessource;
use App\Http\Resources\ArticleVenteCollection;
use App\Http\Resources\CategoryRessource;
use App\Models\ArticleConfection;
use App\Models\ArticleVente;
use App\Models\Category;
use App\Traits\FileUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleVenteController extends Controller
{
    use FileUploaded;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->data) {
            return $this->jsonResponse("", [], [
                "categories_confection" => CategoryRessource::collection(Category::byType("confection")->get()),
                "categories_vente" => CategoryRessource::collection(Category::byType("vente")->get()),
                "articles_confection" => ArticleRessource::collection(ArticleConfection::all())
            ]);
        }

        $perPage = $request->perPage ?? null;
        if ($perPage) {
            return ArticleVente::with("articlesConfection")->orderBy("id", "desc")->paginate(intval($perPage));
        }

        return $this->jsonResponse("", [], new ArticleVenteCollection(ArticleVente::with("articlesConfection")->orderBy("id", "desc")->get()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticleVentePostRequest $request)
    {
        $existing = $this->handleExistingArticle($request);
        if ($existing) {
            return $existing;
        }

        $data = $this->storeImage($request, "articles_vente");
        $dataToReturn = null;

        DB::transaction(function () use ($data, &$dataToReturn) {
            $articlesConfection = $data['articles_confection'];
            unset($data['articles_confection']);
            $newArticle = ArticleVente::create($data);
            ArticleVente::afterCreated($newArticle, $articlesConfection);
            $newArticle->load("articlesConfection");
            $dataToReturn = $this->jsonResponse("Article enregistré avec succès", [], [$newArticle]);
        });
        return $dataToReturn;
    }

    /**
     * Display the specified resource.
     */
    public function show(ArticleVente $articleVente)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ArticleVente $articleVente)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ArticleVente $articleVente)
    {
        //
    }

    public function handleExistingArticle(Request $request)
    {
        /** @var ArticleConfection $article */
        $article = ArticleVente::withTrashed()->byLabel($request->label)->first();
        if (!$article) {
            return null;
        }

        if (!$article->trashed()) {
            return $this->jsonResponse("", ["Le nom de l'article indiqué existe déjà"]);
        }

        $article->restore();
        return $this->jsonResponse("Article enregistré avec succès", [], [$article]);
    }
}
