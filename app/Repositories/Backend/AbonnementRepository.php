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

    // Caluler la durée en jours
    public function totalDay(string $type_time){

        //Si c'est semaine
        if($type_time == "S"){
            return 7;
        }

        //Si c'est le mois
        if($type_time == "M"){
            return 30;
        }

        //Si c'est le année
        if($type_time == "A"){
            return 365;
        }

        return 1;
    }

    /**created user */
    public function created($data = array()) {
        //defininir création de user
        $abonnement = $this->model;
        
        $abonnement->name= $data['name'];
        $abonnement->time= $data['time'] * $this->totalDay($data['type_time']);
        $abonnement->type_time= $data['type_time'];
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
        
        $abonnement->name= $data['name'];
        $abonnement->time= $data['time'] * $this->totalDay($data['type_time']);
        $abonnement->type_time= $data['type_time'];
        $abonnement->price= $data['price'];
        $abonnement->type= $data['type'];
        $abonnement->is_actived= $data['is_actived'];
        if (isset($data['hight_lite'])) {
            $abonnement->hight_lite= $data['hight_lite'];
        }
        $abonnement->save();

        return $abonnement;
    }

    /**destroy user */
    public function destroy($id) {
        //defininir destroy de user
        
    }

}
