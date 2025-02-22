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
    public function changeStatus($arrayModePayment){

        $modepaiement = $this->model->get();

        $check = false;
        if(isset($modepaiement)){
            foreach ($modepaiement as $paiment) {
                if(array_key_exists($paiment->code, $arrayModePayment))
                {
                    $paiment->is_active= $arrayModePayment[$paiment->code];
                    $paiment->save();
                    $check = true;
                }
            }
        }
        if($check)
        {
            return true;
        }
    }
    

}
