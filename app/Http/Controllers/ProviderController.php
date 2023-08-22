<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{

    private string $searchNamePattern = "/^[a-zA-Z ]{3,}$/";
    private string $searchPhonePattern = "/^(7[76508]{1})(\\d{1,})$/";

    public function index()
    {
        return $this->jsonResponse("Fournisseurs récupérées avec succès", [], Provider::all());
    }

    public function show(string $id)
    {
        $provider = Provider::find($id);
        if (!$provider) {
            return $this->jsonResponse("", ["Le fournisseur que vous chercher n'existe pas"], []);
        }
        return $this->jsonResponse("Fournisseur récupérer avec succès", [], [$provider]);
    }

    public function store(Request $request)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            "fullname" => "required|min:5",
            "phone" => "required|regex:/^(7[76508]{1})(\\d{7})$/"
        ], [
            "fullname.required" => "Le nom du fournisseur est requis",
            "fullname.min" => "Le nom du fournisseur doit être au minimum 5 caractères",
            "phone.required" => "Le numéro de téléphone du fournisseur est requis",
            "phone.regex" => "Le numéro de téléphone indiqué n'est pas valide"
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse("", $validator->errors());
        }

        /** @var Provider $category */
        $provider = Provider::withTrashed()->byPhone($request->phone)->first();
        if ($provider) {
            if (!$provider->trashed()) {
                return $this->jsonResponse("", ["Le numéro de téléphone indiqué existe déjà"]);
            }

            $provider->restore();
            return $this->jsonResponse("Fournisseur enregistré avec succès", [], [$provider]);
        }

        return $this->jsonResponse("Fournisseur enregistré avec succès", [], [
            Provider::create([
                "fullname" => $request->fullname,
                "phone" => $request->phone,
            ])
        ]);
    }

    public function update(Request $request, string $id)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            "fullname" => "sometimes|required|min:5",
            "phone" => "sometimes|required|regex:/^(7[76508]{1})(\\d{7})$/"
        ], [
            "fullname.required" => "Le nom du fournisseur est requis",
            "fullname.min" => "Le nom du fournisseur doit être au minimum 5 caractères",
            "phone.required" => "Le numéro de téléphone du fournisseur est requis",
            "phone.regex" => "Le numéro de téléphone indiqué n'est pas valide"
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse("", $validator->errors());
        }

        /** @var Provider $category */
        $category = Provider::where("id", $id)->first();
        $category->update($request->only("fullname", "phone"));
        return $this->jsonResponse("Fournisseur modifié avec succès", [], [$category]);
    }

    public function search(string $searchValue)
    {
        if (!$searchValue) {
            return $this->jsonResponse("", ["Le clé de recherche est requis !"]);
        }

        if (strlen($searchValue) < 3) {
            return $this->jsonResponse("", ["Pour que la recherche puisse aboutir la clé de recherche doit contenir au moins 3 caractères"]);
        }

        if (preg_match($this->searchPhonePattern, $searchValue)) {
            $providers = Provider::where("phone", "like", "$searchValue%")->get();
            if ($providers->isEmpty()) {
                return $this->jsonResponse("", ["Aucun numéro ne correspond à votre recherche"]);
            }
    
            return $this->jsonResponse("Fournisseurs trouvées", [], [$providers]);
        }

        if (preg_match($this->searchNamePattern, $searchValue)) {
            $providers = Provider::where("fullname", "like", "$searchValue%")->get();
            if ($providers->isEmpty()) {
                return $this->jsonResponse("", ["Aucun fournisseur ne correspond à votre recherche"]);
            }
    
            return $this->jsonResponse("Fournisseurs trouvées", [], $providers);
        }
    }
}
