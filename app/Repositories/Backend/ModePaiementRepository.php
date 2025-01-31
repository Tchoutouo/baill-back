<?php

namespace App\Repositories\Backend;
use App\Models\ModePaiement;
use App\Repositories\ResourcesRepository;
use Illuminate\Http\Request;

class ModePaiementRepository   extends ResourcesRepository
{

    public function __construct(ModePaiement $modepaiement) {
        $this->model = $modepaiement;
    }

    public function getById($id) {
        $modepaiement = $this->model->find($id);
        return $modepaiement;
    }

    public function getAll($adv = null) {

        if($adv){
            return $this->model->where('is_active',true)->get();
        }
        
        return $this->model->get();
    }

    
    //** Change status */
    public function changeStatus($id){
        $modepaiement = $this->model->find($id);

        if(isset($modepaiement)){
            $modepaiement->is_active= !$modepaiement->is_active;
            $modepaiement->save();
            return true;
        }
    }
    

}
