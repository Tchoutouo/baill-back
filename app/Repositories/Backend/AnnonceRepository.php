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
        $annonce = $this->model->where('id', $id)->first();
        return $annonce;
    }

    /**created annonce */
    public function created($data = array()) {
        //defininir création de annonce
        
        $annonce = $this->model;
        
        $annonce->title= $data['title'];
        $annonce->subtitle= $data['subtitle'];
        $annonce->description= $data['description'];
        $annonce->price= $data['price'];
        $annonce->contact= $data['contact'];
        $annonce->country= $data['country'];
        $annonce->neighborhood= $data['neighborhood'];
        $annonce->is_published= $data['is_published'];
        $annonce->status= $data['status'];
        $annonce->is_forward= $data['is_forward'];
        $annonce->location= "Centre";
        $annonce->user_id= $data['user_id'];
        $annonce->abonnement_id= $data['abonnement_id'];

        $annonceTable = [
            'annonce'=> $annonce,
            'categorie_id'=> $data['categorie_id'],
        ];

        $annonce->save();

        return $annonceTable;
    }

    /**updated annonce */
    public function updated($data = array(), $id) {
        //defininir update de annonce                
        $annonce = $this->model->find($id);
        
        $annonce->title= $data['title'];
        $annonce->subtitle= $data['subtitle'];
        $annonce->description= $data['description'];
        $annonce->price= $data['price'];
        $annonce->contact= $data['contact'];
        $annonce->country= $data['country'];
        $annonce->neighborhood= $data['neighborhood'];
        $annonce->is_published= $data['is_published'];
        $annonce->status= $data['status'];
        $annonce->is_forward= $data['is_forward'];

        $annonceTable = [
            'annonce'=> $annonce,
            'user_id'=> $data['user_id'],
            'abonnement_id'=> $data['abonnement_id'],
        ];

        $annonce->save();

        return $annonceTable;
    }

    /**destroy annonce */
    public function destroy($id) {
        //defininir destroy de annonce

        // $annonce = $this->model->find($id);
        // $annonce->categories()->detach();
        
    }

}
