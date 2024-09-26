<?php

namespace App\Repositories\Backend;
use App\Models\Categorie;
use App\Repositories\ResourcesRepository;
use Illuminate\Http\Request;

use function PHPSTORM_META\type;

class CategorieRepository   extends ResourcesRepository
{

    public function __construct(Categorie $categorie) {
        $this->model = $categorie;
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
        $array_sous = $data["array_sous"];

        /** Remplir la table pivot categorie_sous_categories */
        // dd(($array_sous));
        if(isset($array_sous) && !empty($array_sous)){
              for ($i=0; $i < count($array_sous); $i++) { 
                $categorie->sousCategorie()->attach($array_sous[$i]);
              }
        }
    
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

}
