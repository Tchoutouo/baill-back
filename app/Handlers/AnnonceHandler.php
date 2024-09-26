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
                $categorie_id = $inputs['categorie_id'];
                $annonce = $inputs['annonce'];
                // dd($annonce);
                $annonce->categories()->attach($categorie_id);
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