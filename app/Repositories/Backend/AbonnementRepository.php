<?php

namespace App\Repositories\Backend;
use App\Models\Abonnement;
use App\Repositories\ResourcesRepository;
use Illuminate\Http\Request;

class AbonnementRepository   extends ResourcesRepository
{

    public function __construct(Abonnement $abonnement) {
        $this->model = $abonnement;
    }

    public function getAll() {
        $abonnement = $this->model->all();
        return $abonnement;
    }

    public function getById($id) {
        $abonnement = $this->model->where('id', $id)->first();
        return $abonnement;
    }

    public function check_account($id){
        $abonnement = $this->model->where('id', $id)->first()->price;
        return $abonnement;
    }

    /**created user */
    public function created($data = array()) {
        //defininir création de user
        $abonnement = $this->model;
        
        $abonnement->name= $data['name'];
        $abonnement->description= $data['description'];
        $abonnement->time= $data['time'];
        $abonnement->price= $data['price'];
        $abonnement->type= $data['type'];
        $abonnement->is_actived= $data['is_actived'];
        if (isset($data['hight_lite'])) {
            $abonnement->hight_lite= $data['hight_lite'];
        }
        $abonnement->save();

        return $abonnement;
    }

    /**updated user */
    public function updated($data = array(), $id) {
        //defininir update de user

        $abonnement = $this->model->find($id);
        
        $abonnement->name= $data['username'];
        $abonnement->username= $data['username'];
        $abonnement->last_name= $data['last_name'];
        $abonnement->first_name= $data['last_name'];
        $abonnement->email= $data['email'];
        $abonnement->whatsapp_number= $data['whatsapp_number'];
        $abonnement->country= $data['country'];
        $abonnement->city= $data['city'];
        $abonnement->neighborhood= $data['neighborhood'];
        $abonnement->password= $data['password'];
        $abonnement->save();

        return $abonnement;
    }

    /**destroy user */
    public function destroy($id) {
        //defininir destroy de user
        
    }

}
