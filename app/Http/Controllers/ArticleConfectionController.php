<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Provider;
use App\Traits\FileUploaded;
use Illuminate\Http\Request;
use App\Models\Approvisionnement;
use App\Models\ArticleConfection;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ArticlePutRequest;
use App\Http\Resources\ArticleRessource;
use App\Http\Requests\ArticlePostRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\CategoryRessource;
use App\Http\Resources\ProviderRessource;

class ArticleConfectionController extends Controller
{
    use FileUploaded;

    public function index(Request $request)
    {
        if ($request->data) {
            return $this->jsonResponse("", [], [
                "providers" => ProviderRessource::collection(Provider::all()),
                "categories" => CategoryRessource::collection(Category::byType("confection")->get())
            ]);
        }

        $perPage = $request->perPage ?? null;
        if ($perPage) {
            return new ArticleCollection(ArticleConfection::with("category", "providers")->orderBy("id", "desc")->paginate(intval($perPage)));
        }

        return $this->jsonResponse("", [], new ArticleCollection(ArticleConfection::with("category", "providers")->orderBy("id", "desc")->get()));
    }

    public function show(string $id)
    {
        /** @var ArticleConfection $article */
        $article = ArticleConfection::find($id);
        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous chercher n'existe pas!"]);
        }
        $article->load("category", "providers");
        return $this->jsonResponse("Article trouvée avec succès", [], [$article]);
    }

    public function store(ArticlePostRequest $request)
    {
        $existing = $this->handleExistingArticle($request);
        if ($existing) {
            return $existing;
        }

        $data = $this->storeImage($request, "articles_confection");
        $dataToReturn = null;

        DB::transaction(function () use ($data, &$dataToReturn) {
            $providers = explode(",", $data['providers']);
            $order = $data['order'];
            unset($data['providers'], $data['order']);

            /** @var ArticleConfection $newArticle */
            $newArticle = ArticleConfection::create($data);
            ArticleConfection::afterCreated($newArticle, $providers, $order);
            $newArticle->load("category", "providers");
            $dataToReturn = $this->jsonResponse("Article enregistré avec succès", [], [new ArticleRessource($newArticle)]);
        });
        return $dataToReturn;
    }

    public function update(ArticleConfection $article, ArticlePutRequest $request)
    {
        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous essayer de modifier n'existe pas"]);
        }
        
        $data = $this->storeOrReplace($article, $request, "articles_confection");
        if (empty($data)) {
            return $this->jsonResponse("", ["Aucune modification"]);
        }

        DB::transaction(function () use ($article, $data) {
            $providers = array_key_exists('providers', $data) ? explode(",", $data['providers']) : null;
            $order = array_key_exists('order', $data) ? $data['order'] : null;
            unset($data['providers'], $data['order']);

            $article->update($data);
            ArticleConfection::afterUpdated($article, $providers, $order);
        });
        $article->load("category", "providers");
        return $this->jsonResponse("Article modifié avec succès", [], [new ArticleRessource($article)]);
    }

    public function handleExistingArticle(Request $request)
    {
        /** @var ArticleConfection $article */
        $article = ArticleConfection::withTrashed()->byLabel($request->label)->first();
        if (!$article) {
            return null;
        }

        if (!$article->trashed()) {
            return $this->jsonResponse("", ["Le nom de l'article indiqué existe déjà"]);
        }

        $article->restore();
        return $this->jsonResponse("Article enregistré avec succès", [], [$article]);
    }

    public function searchArticle(string $label)
    {
        $article = ArticleConfection::where("label", "like", "$label")->first();
        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous chercher n'existe pas"]);
        }
        return $this->jsonResponse("Article trouvé", [], [$article]);
    }

    public function destroy(string $id)
    {
        $article = ArticleConfection::where("id", $id)->first();

        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous essayer de supprimer n'existe pas"]);
        }

        $destroyed = DB::transaction(function () use ($id) {
            ArticleConfection::destroy($id);
            $approvisionnements = Approvisionnement::where("article_id", $id)->get()->pluck('article_id');
            Approvisionnement::destroy($approvisionnements);
        });
        return $this->jsonResponse("L'article a été supprimé avec succès", [], [$destroyed]);
    }
}
