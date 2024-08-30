<?php

namespace App\Repositories\Backend;
use App\Models\Categorie;
use App\Repositories\ResourcesRepository;
use Illuminate\Http\Request;

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
    }

    /**updated categorie */
    public function updated($data = array(), $id) {
        //defininir update de categorie
    }

    /**destroy categorie */
    public function destroy($id) {
        //defininir destroy de categorie
    }

}
