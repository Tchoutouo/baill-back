<?php

namespace App\Handlers;

use Exception;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Backend\AnnonceRepository;
// use App\Notifications\SendLearnerRegistrationNotification;

class AnnonceHandler  {

    protected $annonceRepository;

    public  function __construct(AnnonceRepository $annonceRepository){

        $this->annonceRepository = $annonceRepository;

    }

    public function store ($inputs){
        try {

            $result = DB::transaction( function() use ($inputs){
                //dd($inputs['categorie']);
                $categorie = $inputs['categorie'];
                $annonce = $inputs['annonce'];
                for ($i=0; $i < count($categorie); $i++) { 
                    $annonce->categories()->attach($categorie[$i]);
                }
                return $annonce;
            });
            DB::commit();

        }catch(Exception $e) {

            DB::rollBack();
            
        }

        if(isset($result)){

            return $result;

        }else{

        }

    }

}