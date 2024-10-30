<?php

namespace App\Http\Controllers\Api\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Picture;
use Illuminate\Support\Str;

class PictureController extends Controller
{
    //
    function  storePicture($request, $annonce_id){
        
        /** Pour chaque image lié à l'annonce créer un enregistrement */
        if ($request->hasFile('images')) {
                $images = $request->file('images');
                // dd($images);
                $paths = [];
                
                foreach ($images as $image) {
                    $path = $image->store('images', 'public');
                    Picture::create([
                        "location"=> $path,
                        'annonce_id'=> $annonce_id,
                    ]);
                    $paths[] = $path;
                }
                
            return true;
        }
    }

    // Update des images d'une annonces
    function  updated($request, $annonce_id){
        
        // Supprimer les anciennes images 
        Picture::where('annonce_id', $annonce_id)->delete();

        /** Pour chaque nouvelle image lié à l'annonce créer un enregistrement */
        if ($request->hasFile('images')) {
                $images = $request->file('images');
                $paths = [];
                
                foreach ($images as $image) {
                    $path = $image->store('images', 'public');
                    Picture::create([
                        "location"=> $path,
                        'annonce_id'=> $annonce_id,
                    ]);
                    $paths[] = $path;
                }
            return true;
        }
    }


    // Get images the l'annonce

    function getImage($id){
        
        $allImages = Picture::select('location')->where('annonce_id', $id)->get();
        return $allImages;
    }
}
