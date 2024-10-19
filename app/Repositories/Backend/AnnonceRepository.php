<?php

namespace App\Repositories\Backend;
use App\Models\Annonce;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\ResourcesRepository;
use App\Http\Controllers\Api\Backend\PictureController;
use Illuminate\Http\Request;
use Laravel\Sail\Console\PublishCommand;

class AnnonceRepository   extends ResourcesRepository
{
    protected $abonnementRepository;
    protected $pictureController;

    public function __construct(Annonce $annonce, AbonnementRepository $abonnementRepository, PictureController $pictureController) {
        $this->model = $annonce;
        $this->abonnementRepository = $abonnementRepository;
        $this->pictureController = $pictureController;
    }


    /**created annonce */
    public function created($data = array()) {
        //defininir création de annonce
        
        $annonce = $this->model;
        
        $annonce->title= $data['title'];
        // $annonce->subtitle= $data['subtitle'];
        $annonce->description= $data['description'];
        $annonce->price= $data['price'];
        // $annonce->contact= $data['contact'];
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

        $annonce->location= $data['location'];
        $annonce->user_id= $data['user_id'];
        $annonce->abonnement_id= $data['abonnement_id'];

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
        $annonce->abonnement_id= $data['abonnement_id'];

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


    function getAllAnnonce($user_id) {

        // Récupération des annonces pour un utilisateur
        $arrayAnnonce = $this->model->where('user_id', $user_id)->get();

        // Vérifiez si la collection est vide
        if ($arrayAnnonce->isNotEmpty()) {
            foreach ($arrayAnnonce as $key => $annonce) {
                $picture  = $this->pictureController->getImage($annonce->id);
                $image = [];

                //Parcourir chaque image add à son annonce
                foreach ($picture as $key => $pict) {
                    $image[] = $pict->location;
                }
                $annonce['url_image']= $image;  
            }

            return $arrayAnnonce;
        }
    }


    // Count nombre d'annonce publier
    function getAnnoncePublisher($user_id){
        // Récupération des annonces pour un utilisateur
        $annoncePublisher = $this->model->where('user_id', $user_id)->where('status', 3)->count();
        return $annoncePublisher;
    }
    
    // Count nombre d'annonce expirer
    function getAnnonceExpired($user_id){
        // Récupération des annonces pour un utilisateur
        $annonceExpired= $this->model->where('user_id', $user_id)->where('status', 0)->count();
        return $annonceExpired;
    }
    
    // Count nombre d'annonce en cours
    function getAnnonceInProgress($user_id){
        // Récupération des annonces pour un utilisateur
        $annonceInProgress= $this->model->where('user_id', $user_id)->where('status', 1)->count();
        return $annonceInProgress;
    }
    
    // Count nombre d'annonce en pause
    function getAnnoncePause($user_id){
        // Récupération des annonces pour un utilisateur
        $annoncePause= $this->model->where('user_id', $user_id)->where('status', 2)->count();
        return $annoncePause;
    }
    

    // Change status annonces
    function changeStatusAnnonce($user_id, $annonce_id, $new_status){
        $annonce = $this->model->where('user_id', $user_id)->where('id', $annonce_id)->first();
        if(isset($annonce))
        {
            $annonce->update([
                'status' => $new_status
            ]);
            return true;
        }
    }


    //** Fonction côté visiteur */

    // Tous les annones homepage
    function getAllAnnonceFront(){
        $arrayAnnonce = $this->model->where('status','!=','0')->get(); //éviter les annonces expirées

        // Vérifiez si la collection est vide
        if ($arrayAnnonce->isNotEmpty()) {
            foreach ($arrayAnnonce as $key => $annonce) {
                $picture  = $this->pictureController->getImage($annonce->id);
                $image = [];

                //Parcourir chaque image add à son annonce
                foreach ($picture as $key => $pict) {
                    $image[] = $pict->location;
                }
                $annonce['url_image']= $image;  
            }

            return $arrayAnnonce;
        }
    }

    // Tous les annones à la une homepage
    function getAnnonceUne(){
        $arrayAnnonce = $this->model->with('abonnements')->where('status','!=','0')
                                    ->whereHas('abonnements', function($query){
                                        $query->where('price','>',0);
                                    })->get();

        // Vérifiez si la collection est vide
        if ($arrayAnnonce->isNotEmpty()) {
            foreach ($arrayAnnonce as $key => $annonce) {
                $picture  = $this->pictureController->getImage($annonce->id);
                $image = [];

                //Parcourir chaque image add à son annonce
                foreach ($picture as $key => $pict) {
                    $image[] = $pict->location;
                }
                $annonce['url_image']= $image;  
            }

            return $arrayAnnonce;
        }
    }
}
