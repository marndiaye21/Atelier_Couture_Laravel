<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleVentePostRequest;
use App\Http\Requests\ArticleVentePutRequest;
use App\Http\Resources\ArticleRessource;
use App\Http\Resources\ArticleVenteCollection;
use App\Http\Resources\ArticleVenteRessource;
use App\Http\Resources\CategoryRessource;
use App\Models\ArticleConfection;
use App\Models\ArticleConfection_ArticleVente;
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
                "categories_vente" => CategoryRessource::collection(Category::byType("vente")->get()),
                "articles_confection" => ArticleRessource::collection(ArticleConfection::with('category')->get())
            ]);
        }

        $perPage = $request->perPage ?? null;
        if ($perPage) {
            return new ArticleVenteCollection(ArticleVente::with("articlesConfection", "category")->orderBy("id", "desc")->paginate(intval($perPage)));
        }

        return $this->jsonResponse("", [], new ArticleVenteCollection(
            new ArticleVenteCollection(ArticleVente::with("articlesConfection", "category")->orderBy("id", "desc")->get())
        ));
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

        return DB::transaction(function () use ($data) {
            $articlesConfection = $data['articles_confection'];
            unset($data['articles_confection']);

            $newArticle = ArticleVente::create($data);
            ArticleVente::afterCreated($newArticle, $articlesConfection);
            $newArticle->load("articlesConfection", "category");
            return $this->jsonResponse("Article enregistré avec succès", [], [new ArticleVenteRessource($newArticle)]);
        });
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
    public function updateArticle(ArticleVentePutRequest $request, ArticleVente $articleVente)
    {
        /** @var ArticleVente $articleVente */
        if (!$articleVente) {
            return $this->jsonResponse("", ["L'article que vous essayer de modifier n'existe pas"]);
        }
        
        $data = $this->storeOrReplace($articleVente, $request, "articles_vente");
        if (empty($data)) {
            return $this->jsonResponse("", ["Aucune modification"]);
        }
        
        DB::transaction(function () use ($articleVente, $data) {
            $articlesConfection = array_key_exists('articles_confection', $data) ? $data['articles_confection'] : null;
            unset($data['articles_confection']);

            $articleVente->update($data);
            ArticleVente::afterUpdated($articleVente, $articlesConfection);
        });
        $articleVente->load("articlesConfection", "category");
        return $this->jsonResponse("Article modifié avec succès", [], [new ArticleVenteRessource($articleVente)]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /** @var ArticleVente $article */
        $article = ArticleVente::where("id", $id)->first();
        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous essayer de supprimer n'existe pas"]);
        }

        DB::transaction(function () use ($article) {
            $article->delete();
            $ids = ArticleConfection_ArticleVente::where("article_vente_id", $article->id)->get('id')->pluck('id');
            ArticleConfection_ArticleVente::destroy($ids);
        });
        return $this->jsonResponse("L'article a été supprimé avec succès", [], [1]);
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
