<?php

namespace App\Traits;

use App\Http\Requests\ArticlePostRequest;
use App\Http\Requests\ArticlePutRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait FileUploaded
{
    function storeImage(Request $request, string $folder)
    {
        $data = $request->validated();
        /** @var UploadedFile $image */
        $file = $request->hasFile('photo');
        if ($file) {
            $image = $request->file('photo');
            $data['photo'] = $image->store($folder, "public");
        }

        return $data;
    }

    function storeOrReplace(Model $model, Request $request, string $folder)
    {
        if ($request->hasFile('photo')) {
            $model->photo ? Storage::disk("public")->delete($model->photo) : null;
        }

        return $this->storeImage($request, $folder);
    }
}
