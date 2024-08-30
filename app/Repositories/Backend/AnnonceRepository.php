<?php

namespace App\Repositories\Backend;
use App\Models\Annonce;
use App\Repositories\ResourcesRepository;
use Illuminate\Http\Request;

class AnnonceRepository   extends ResourcesRepository
{

    public function __construct(Annonce $annonce) {
        $this->model = $annonce;
    }

    public function getById($id) {
        $annonce = $this->model->where('id', $id)->with('profils')->first();
        return $annonce;
    }

    /**created annonce */
    public function created($data = array()) {
        //defininir création de annonce
    }

    /**updated annonce */
    public function updated($data = array(), $id) {
        //defininir update de annonce
    }

    /**destroy annonce */
    public function destroy($id) {
        //defininir destroy de annonce
    }

}
