<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Provider;
use App\Traits\FileUploaded;
use Illuminate\Http\Request;
use App\Models\Approvisionnement;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ArticlePostRequest;
use App\Http\Requests\ArticlePutRequest;

class ArticleController extends Controller
{
    use FileUploaded;

    public function index(Request $request)
    {
        $perPage = $request->perPage ?? null;
        if ($perPage) {
            if ($request->article_data) {
                return $this->jsonResponse("", [], [
                    "articles" => Article::with(["category", "providers"])->orderBy("id", "desc")->paginate(intval($perPage)),
                    "providers" => Provider::all(),
                    "categories" => Category::all(),
                    "approvisionnements" => Approvisionnement::all()
                ]);
            }

            return $this->jsonResponse("", [], Article::with("category")->orderBy("id", "desc")->paginate(intval($perPage)));
        }

        return $this->jsonResponse("", [], Article::with("category")->orderBy("id", "desc")->get());
    }

    public function show(string $id)
    {
        $article = Article::find($id);
        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous chercher n'existe pas!"]);
        }
        return $this->jsonResponse("Article trouvée avec succès", [], [$article]);
    }

    public function store(ArticlePostRequest $request)
    {
        $existing = $this->handleExistingArticle($request);
        if ($existing) {
            return $existing;
        }

        $data = $this->storeImage($request, "article");
        $dataToReturn = null;

        DB::transaction(function () use ($data, &$dataToReturn) {
            $providers = explode(",", $data['providers']);
            $order = $data['order'];
            unset($data['providers'], $data['order']);

            $newArticle = Article::create($data);
            Article::afterCreated($newArticle, $providers, $order);
            $newArticle->load("category", "providers");
            $dataToReturn = $this->jsonResponse("Article enregistré avec succès", [], [$newArticle]);
        });
        return $dataToReturn;
    }

    public function update(Article $article, ArticlePutRequest $request)
    {
        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous essayer de modifier n'existe pas"]);
        }
        
        $data = $this->storeOrReplace($article, $request, "article");
        if (empty($data)) {
            return $this->jsonResponse("", ["Aucune modification"]);
        }

        DB::transaction(function () use ($article, $data) {
            $providers = array_key_exists('providers', $data) ? explode(",", $data['providers']) : null;
            $order = array_key_exists('order', $data) ? $data['order'] : null;
            unset($data['providers'], $data['order']);

            $article->update($data);
            Article::afterUpdated($article, $providers, $order);
        });
        $article->load("category", "providers");
        return $this->jsonResponse("Article modifié avec succès", [], [$article]);
    }

    public function handleExistingArticle(Request $request)
    {
        /** @var Article $article */
        $article = Article::withTrashed()->byLabel($request->label)->first();
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
        $article = Article::where("label", "like", "$label")->first();
        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous chercher n'existe pas"]);
        }
        return $this->jsonResponse("Article trouvé", [], [$article]);
    }

    public function destroy(string $id)
    {
        $article = Article::where("id", $id)->first();

        if (!$article) {
            return $this->jsonResponse("", ["L'article que vous essayer de supprimer n'existe pas"]);
        }

        $destroyed = DB::transaction(function () use ($id) {
            Article::destroy($id);
            $approvisionnements = Approvisionnement::where("article_id", $id)->get()->pluck('article_id');
            Approvisionnement::destroy($approvisionnements);
        });
        return $this->jsonResponse("L'article a été supprimé avec succès", [], [$destroyed]);
    }
}
