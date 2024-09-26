<?php

namespace App\Repositories\Backend;
use App\Models\SousCategorie;
use App\Repositories\ResourcesRepository;
use Illuminate\Http\Request;

class SousCategorieRepository   extends ResourcesRepository
{

    public function __construct(SousCategorie $sous_categories) {
        $this->model = $sous_categories;
    }

    public function getById($id) {
        $sous_categories = $this->model->find($id);
        return $sous_categories;
    }

    /**created sous-categorie */
    public function created($data = array()) {
        
        //defininir création de sous-categorie
        $sous_categories = $this->model;
        
        $sous_categories->title= $data['title'];
        $sous_categories->description= $data['description'];
        $sous_categories->save();
        $array_categorie = $data["array_cat"];

        /** Remplir la table pivot categorie_sous_categories */
        if(isset($array_categorie) && !empty($array_categorie)){
              for ($i=0; $i < count($array_categorie); $i++) {
                $sous_categories->categorie()->attach($array_categorie[$i]);
              }
        }

        return $sous_categories;
    }

    /**updated categorie */
    public function updated($data = array(), $id) {
        //defininir update de sous-categorie
        $sous_categories = $this->model->find($id);
        
        //dd($sous_categories);
        if(isset($sous_categories)){
            $sous_categories->title= $data['title'];
            $sous_categories->description= $data['description'];
            $sous_categories->save();
            $array_categorie = $data["array_cat"];

            /** Remplir la table pivot categorie_sous_categories */
            // dd(($array_sous));
            if(isset($array_categorie) && !empty($array_categorie)){
                  for ($i=0; $i < count($array_categorie); $i++) { 
                    $sous_categories->Categorie()->sync($array_categorie[$i]);
                  }
            }
        }

        return $sous_categories;
    }

    /**destroy categorie */
    public function destroy($id) {
        //defininir destroy de sous-categorie
        $sous_categories = $this->model->find($id);

        if(isset($sous_categories)){
            $sous_categories = $sous_categories->delete();
            return $sous_categories;
        }
    }

}
