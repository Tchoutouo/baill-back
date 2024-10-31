<?php

namespace App\Repositories\Backend;
use App\Models\Categorie;
use App\Models\User;
use App\Repositories\ResourcesRepository;
use App\Http\Controllers\Api\Backend\PictureController;
use Illuminate\Http\Request;

use function PHPSTORM_META\type;

class CategorieRepository   extends ResourcesRepository
{

    private $pictureController;

    public function __construct(Categorie $categorie, PictureController $pictureController) {
        $this->model = $categorie;
        $this->pictureController = $pictureController;
    }

    public function getAll() {
        $categorie = $this->model->get();
        return $categorie;
    }

    public function getById($id) {
        $categorie = $this->model->where('id', $id)->with('profils')->first();
        return $categorie;
    }

    /**created categorie */
    public function created($data = array()) {
        //defininir création de categorie
        
        $categorie = $this->model;
        $categorie->title= $data['title'];
        $categorie->description= $data['description'];
        $categorie->save();
        // $array_sous = $data["array_sous"];

        // /** Remplir la table pivot categorie_sous_categories */
        // // dd(($array_sous));
        // if(isset($array_sous) && !empty($array_sous)){
        //       for ($i=0; $i < count($array_sous); $i++) { 
        //         $categorie->sousCategorie()->attach($array_sous[$i]);
        //       }
        // }
    
        return $categorie;
    }

    /**updated categorie */
    public function updated($data = array(), $id) {
        //defininir update de categorie
        $data["array_sous"] = [4];
        $categorie = $this->model->find($id);

        if(isset($categorie)){
            $categorie->title= $data['title'];
            $categorie->description= $data['description'];
            $categorie->save();
            $array_sous = $data["array_sous"];

            /** Remplir la table pivot categorie_sous_categories */
            
            if(isset($array_sous) && !empty($array_sous)){
                  for ($i=0; $i < count($array_sous); $i++) { 
                    $categorie->sousCategorie()->sync($array_sous[$i]);
                  }
            }
        }

        return $categorie;
    }

    /**destroy categorie */
    public function destroy($id) {
        //defininir destroy de categorie
        $categories = $this->model->find($id);

        if(isset($categories)){
            $categories = $categories->delete();
            return $categories;
        }
    }


    public function getAnnonceCateg($arrayCateg){

        for ($i=0; $i < count($arrayCateg); $i++) {

            $categorie = $this->model->with('annonces')->find($arrayCateg[$i]);

            $annonces = $categorie->annonces()->get();
            $arrayCa = [];

            foreach ($annonces as $annonce) {
                $arrayCa[]= $categorie->title;
                
                $picture  = $this->pictureController->getImage($annonce->id);
                $image = [];
                
                //Parcourir chaque image add à son annonce
                foreach ($picture as $pictu) {
                    $image[] = $pictu->location;
                }
                
                $annonce['url_image']= $image;
                $annonce['users'] = User::find($annonce->user_id);
                $annonce['categorie'] = $arrayCa;
            }
        }

        return $annonces;
    }

}
