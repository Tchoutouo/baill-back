<?php

namespace App\Repositories\Backend;
use App\Models\Annonce;
use App\Models\Picture;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\ResourcesRepository;
use App\Http\Controllers\API\Backend\PictureController;
use Illuminate\Support\Carbon;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Laravel\Sail\Console\PublishCommand;
use Nette\Utils\Random;

class AnnonceRepository   extends ResourcesRepository
{
    protected $abonnementRepository;
    protected $pictureController;

    public function __construct(Annonce $annonce, AbonnementRepository $abonnementRepository, PictureController $pictureController) {
        $this->model = $annonce;
        $this->abonnementRepository = $abonnementRepository;
        $this->pictureController = $pictureController;
    }

    /**count annonces */
    public function countAnnonce(){

        $annonce = $this->model->count();
        
        return $annonce;
    }

    /**created annonce */
    public function created($data = array()) {
        //defininir création de annonce
        
        $annonce = $this->model;
        
        $annonce->title= $data['title'];
        // $annonce->subtitle= $data['subtitle'];
        $annonce->description= $data['description'];
        $annonce->price= $data['price'];
        $annonce->reference = "ANNONCE_" . date('Y_His');
        // $annonce->contact= $data['contact'];
        $annonce->country= $data['country'];
        $annonce->neighborhood= $data['neighborhood'];
        // $annonce->is_published= $data['is_published'];


        // if l'abonnement n'est pas free mettre en avant l'annonce
        if($this->abonnementRepository->check_account($data['abonnement_id'])){
            if(isset($data['is_forward'])){
                $annonce->is_forward = $data['is_forward'];
            }
        }

        // if l'abonnement est free mettre en avant l'annonce
        if($this->abonnementRepository->check_account($data['abonnement_id']) == 0 ){
            if(isset($data['status'])){
                $annonce->status = 3;
            }
        }

        $annonce->location= $data['location'];
        $annonce->user_id= $data['user_id'];

        if (isset($data['abonnement_id'])) {
            $annonce->abonnement_id= $data['abonnement_id'];
        }

        $annonceTable = [
            'annonce'=> $annonce,
            'categorie'=> $data['categorie'],
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
        // $annonce->is_published= $data['is_published'];
        if(isset($data['status'])){
            $annonce->status = $data['status'];
        }

        // if l'abonnement n'est pas free mettre en avant l'annonce
        if($this->abonnementRepository->check_account($data['abonnement_id']) > 0){
            if(isset($data['is_forward'])){
                $annonce->is_forward = $data['is_forward'];
            }
        }

        $annonce->location = $data['location'];
        $annonce->user_id= $data['user_id'];

        if (isset($data['abonnement_id'])) {
            $annonce->abonnement_id= $data['abonnement_id'];
        }

        $annonceTable = [
            'annonce'=> $annonce,
            'user_id'=> $data['user_id'],
            'abonnement_id'=> $data['abonnement_id'],
        ];

        $annonce->save();

        return $annonceTable;
    }

    /**destroy annonce */
    public function deleteAnnonce($id, $categorie) {

        $annonce = $this->model->with('categories')->find($id);
        if(!empty($annonce)){
            if($annonce->status === "1"){

                //Supprimer les attâches dans la table pivot
                for ($i=0; $i < count($categorie); $i++) {
                    $annonce->categories()->detach($categorie[$i]);
                }

                // foreach($categorie as $categ){
                //     $annonce->categories()->detach($categ->id);
                // }

                // Supprimer ses images 
                Picture::where('annonce_id', $annonce->id)->delete();

                $annonce->delete();
                return true;
            }
        }
        
    }

    // Tous les annonces liées à un utilisateur
    function getAllAnnonce($user_id = null, $nbr_annonce, $search = null) {

        // Récupération des annonces pour un utilisateur
        $arrayAnnonce = $this->model
                    ->with('categories')
                    ->with('abonnements');

                // Si user_id existe
                if($user_id){
                    $arrayAnnonce = $arrayAnnonce->where('user_id', $user_id);
                }

                // Si $search existe
                if ($search) {
                $arrayAnnonce = $arrayAnnonce->where(function ($q) use ($search) {
                        $q->where('title', 'LIKE', "%$search%")
                        ->orWhereHas('categories', function ($q) use ($search) {
                            $q->where('title', 'LIKE', "%$search%");
                        });
                    });
                }

                $arrayAnnonce = $arrayAnnonce->orderBy('created_at', 'desc')
                ->paginate($nbr_annonce);

        // Vérifiez si la collection est vide
        if ($arrayAnnonce->isNotEmpty()) {
            foreach ($arrayAnnonce as $annonce) {
                
                if ($annonce->status === '1') {
                    $annonce['next_expiration_date'] = '...';
                }else {
                    // Calculer la prochaine date d'expiration en fonction de la durée d'une annonce
                    $nextExpirationDate = Carbon::parse($annonce->created_at)->addDays($annonce->abonnements->time);
                    
                    // Vérifier si cette date est dépassée
                    if (Carbon::now()->greaterThan($nextExpirationDate)) {
                        // Si la date est dépassée, on met "expiré"
                        $annonce['next_expiration_date'] = 'expiré';
                    } else {
                        // Sinon, on retourne la date au format AAAA-MM-JJ
                        $annonce['next_expiration_date'] = $nextExpirationDate->toDateString();
                    }
                }

                if(isset($detail)){
                    $picture  = $this->pictureController->getImage($annonce->id);
                    // $image = [];
    
                    // //Parcourir chaque image add à son annonce
                    // foreach ($picture as $pict) {
                    //     $image[] = $pict->location;
                    // }
                    // $annonce['url_image']= $image;
                    $annonce['url_image']= $picture;

                }
            }

            return $arrayAnnonce;
        }
    }


    function getAnnonce($annonce_id) {
        // Récupération des annonces pour un utilisateur
        $annonce = $this->model->with('users')->with('categories')->with('abonnements')->where('id', $annonce_id)->first();
        
        
        // Vérifiez si la collection est vide
        if (!empty($annonce)) {
                // Calculer la prochaine date d'expiration en fonction de la durée d'une annonce
                $nextExpirationDate = Carbon::parse($annonce->created_at)->addDays($annonce->abonnements->time);
                
                // Vérifier si cette date est dépassée
                if (Carbon::now()->greaterThan($nextExpirationDate)) {
                    // Si la date est dépassée, on met "expiré"
                    $annonce['next_expiration_date'] = 'expiré';
                } else {
                    // Sinon, on retourne la date au format AAAA-MM-JJ
                    $annonce['next_expiration_date'] = $nextExpirationDate->toDateString();
                }

                $picture  = $this->pictureController->getImage($annonce->id);
                // $image = [];

                // //Parcourir chaque image add à son annonce
                // foreach ($picture as $pict) {
                //     $image[] = $pict->location;
                // }
                // $annonce['url_image']= $image;
                $annonce['url_image']= $picture;

            return $annonce;
        }
        return null;
    }


    // Count nombre d'annonce publier
    function getAnnoncePublisher($user_id){
        if ($user_id) {
            // Récupération des annonces pour un utilisateur
            $annoncePublisher = $this->model->where('user_id', $user_id)->where('status', 3)->count();
        }else{
            $annoncePublisher = $this->model->where('status', 3)->count();
        }

        
        return $annoncePublisher;
    }
    
    // Count nombre d'annonce expirer
    function getAnnonceExpired($user_id){

        if ($user_id) {
            // Récupération des annonces pour un utilisateur
            $annonceExpired= $this->model->where('user_id', $user_id)->where('status', 0)->count();
        }
        else {
            $annonceExpired= $this->model->where('status', 0)->count();
        }
        return $annonceExpired;
    }
    
    // Count nombre d'annonce en cours
    function getAnnonceInProgress($user_id){

        if($user_id){
            // Récupération des annonces pour un utilisateur
            $annonceInProgress= $this->model->where('user_id', $user_id)->where('status', 1)->count();
        }else{
            $annonceInProgress= $this->model->where('status', 1)->count();
        }
        return $annonceInProgress;
    }
    
    // Count nombre d'annonce en pause
    function getAnnoncePause($user_id){
        if($user_id){
            // Récupération des annonces pour un utilisateur
            $annoncePause= $this->model->where('user_id', $user_id)->where('status', 2)->count();
        }else{
            $annoncePause= $this->model->where('status', 2)->count();
        }
        return $annoncePause;
    }
    
    //progression status
    public function progressStatusAnnonce(){
        $progressStatus = $this->model
                    ->select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->get();

        $bigTotal = 0;
        foreach($progressStatus as $pro){
            $bigTotal = $bigTotal + $pro->total;
        }

        foreach($progressStatus as $pro){
            $pro->total = ($pro->total * 100 ) / $bigTotal;
        }

        return $progressStatus;
    }

    // Change status annonces
    function changeStatusAnnonce($user_id, $annonce_id, $new_status){
        
        if ($user_id) {
            $annonce = $this->model->where('user_id', $user_id)->where('id', $annonce_id)->first();
        }
        else{
            $annonce = $this->model->where('id', $annonce_id)->first();
        }
        if(isset($annonce))
        {
            $annonce->update([
                'status' => $new_status
            ]);
            return true;
        }
    }

    // Change abonnment annonces
    function changeAbonnementAnnonce($user_id, $annonce_id, $new_abonnement){
        $annonce = $this->model->where('user_id', $user_id)->where('id', $annonce_id)->first();
        if(isset($annonce))
        {
            $annonce->update([
                'abonnement_id' => $new_abonnement
            ]);
            return true;
        }
    }


    //** Fonction côté visiteur */

    // Tous les annones homepage
    function getAllAnnonceFront(){
        $arrayAnnonce = $this->model->with('users')->with('categories')->where('status','3')->paginate(12); //les annonces publiées

        // Vérifiez si la collection est vide
        if ($arrayAnnonce->isNotEmpty()) {
            foreach ($arrayAnnonce as  $annonce) {
                $picture  = $this->pictureController->getImage($annonce->id);
                // $image = [];

                // //Parcourir chaque image add à son annonce
                // foreach ($picture as  $pict) {
                //     if(isset($pict->location)){
                //         $image[] = $pict->location;
                //     }
                // }
                // $annonce['url_image']= $image;  
                $annonce['url_image']= $picture;  

            }

            return $arrayAnnonce;
        }
    }

    // Tous les annones à la une homepage
    function getAnnonceUne(){
        $arrayAnnonce = $this->model->with('users')->with('categories')->with('abonnements')->where('status','3')
                                    ->whereHas('abonnements', function($query){
                                        $query->where('hight_lite',1);
                                    })->get();

        // Vérifiez si la collection est vide
        if ($arrayAnnonce->isNotEmpty()) {
            foreach ($arrayAnnonce as $key => $annonce) {
                $picture  = $this->pictureController->getImage($annonce->id);
                // $image = [];

                // //Parcourir chaque image add à son annonce
                // foreach ($picture as $key => $pict) {
                //     if(isset($pict->location)){
                //         $image[] = $pict->location;
                //     }
                // }
                // $annonce['url_image']= $image;
                $annonce['url_image']= $picture;

            }

            return $arrayAnnonce;
        }
    }

    
    // Trie en fonction des categories/country/city
    function getTrieAnnonce($categ = null, $country = null, $city = null) {
        // Récupération des annonces pour un utilisateur
        $arrayAnnonce = $this->model
                    ->with('users')
                    ->with('categories')
                    ->with('abonnements')
                    ->where('status','3');
                    
                // Si $categorie existe
                if ($categ){
                    $arrayAnnonce = $arrayAnnonce->whereHas('categories', function ($q) use ($categ) {
                        $q->where('title', 'LIKE', "%$categ%");
                    });
                }

                // Si $country existe
                if ($country) {
                    $arrayAnnonce->where('country', 'LIKE', "%$country%");
                }
                
                // Si $city existe
                if ($city) {
                    $arrayAnnonce->where('location', 'LIKE', "%$city%");
                }
                
                $arrayAnnonce = $arrayAnnonce->orderBy('created_at', 'desc')
                ->paginate(12);

        // Vérifiez si la collection est vide
        if ($arrayAnnonce->isNotEmpty()) {
            foreach ($arrayAnnonce as $annonce) {
                $picture  = $this->pictureController->getImage($annonce->id);
                // $image = [];

                // //Parcourir chaque image add à son annonce
                // foreach ($picture as $pict) {
                //     $image[] = $pict->location;
                // }
                // $annonce['url_image']= $image;
                $annonce['url_image']= $picture;

            }

            return $arrayAnnonce;
        }
    }
}
