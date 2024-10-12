<?php

namespace App\Http\Controllers\Api\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Picture;
use Illuminate\Support\Str;

class PictureController extends Controller
{
    //
    function store(Request $request, $location, $annonce_id){

        /** Pour chaque image lié à l'annonce créer un enregistrement */
        for ($i=0; $i < count($location); $i++) {
            $pictureAnnonce = $request->file($location[$i]);
            $userName = Str::uuid() . '.' . $pictureAnnonce->getClientOriginalExtension();
            $request->picture->storeAs('images', $userName);
            Picture::create([
                "location"=> $location[$i],
                'annonce_id'=> $annonce_id,
            ]);
        }
    }
}
