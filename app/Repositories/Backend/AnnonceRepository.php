<?php

namespace App\Repositories\Backend;
use App\Models\Annonce;
use App\Models\Picture;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\ResourcesRepository;
use Illuminate\Support\Carbon;
use \Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentValidateMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class AnnonceRepository   extends ResourcesRepository
{
    protected $abonnementRepository;

    public function __construct(Annonce $annonce, AbonnementRepository $abonnementRepository) {
        $this->model = $annonce;
        $this->abonnementRepository = $abonnementRepository;
    }

    /**count annonces */
    public function countAnnonce(){

        $annonce = $this->model->count();
        
        return $annonce;
    }

    /**created annonce */
    public function created($data = array()) {

        $user_qty_free = Auth::user()->qte_free;
        $annonce = $this->model;
        
        $annonce->title= $data['title'];
        $annonce->description= $data['description'];
        $annonce->price= $data['price'];
        $annonce->reference = "ANNONCE_" . date('Y_His');
        $annonce->country= $data['country'];
        $annonce->neighborhood= $data['neighborhood'];


        // if l'abonnement n'est pas free mettre en avant l'annonce
        if($this->abonnementRepository->check_account($data['abonnement_id'])){
            if(isset($data['is_forward'])){
                $annonce->is_forward = $data['is_forward'];
            }
        }

        // if l'abonnement est free
        if($data['abonnement_id'] === '1' && $user_qty_free > 0){
                $annonce->status = 3;
                $time = $this->abonnementRepository->getById($data['abonnement_id']);
                $annonce->expiration_date = now()->addDays($time->time)->format('Y-m-d');
        }else{
            $annonce->status = 1;
        }

        $annonce->location= $data['location'];
        $annonce->user_id= $data['user_id'];

        if (isset($data['abonnement_id'])) {
            $annonce->abonnement_id= $data['abonnement_id'];
        }
        if (isset($data['validity_period'])) {
            $annonce->validity_period= (int) $data['validity_period'];
        }

        $annonceTable = [
            'annonce'=> $annonce,
            'categorie'=> $data['categorie'],
        ];

        $annonce->save();

        return $annonceTable;
    }

    /**updated annonce */
    public function updated( $id, $data = []) {
        //defininir update de annonce 
        $user_qty_free = Auth::user()->qte_free;               
        $annonce = $this->model->find($id);
        
        $annonce->title= $data['title'];
        $annonce->subtitle= $data['subtitle'];
        $annonce->description= $data['description'];
        $annonce->price= $data['price'];
        $annonce->contact= $data['contact'];
        $annonce->country= $data['country'];
        $annonce->neighborhood= $data['neighborhood'];
        if($data['abonnement_id'] === '1' && $user_qty_free > 0){
                $annonce->status = 3;
                $time = $this->abonnementRepository->getById($data['abonnement_id']);
                $annonce->expiration_date = now()->addDays($time->time)->format('Y-m-d');
        }else{
            $annonce->status = 1;
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

        if (isset($data['validity_period'])) {
            $annonce->validity_period= (int) $data['validity_period'];
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

                // Supprimer ses images 
                Picture::where('annonce_id', $annonce->id)->delete();

                $annonce->delete();
                return true;
            }
        }
        
    }

    // Tous les annonces liées à un utilisateur
    function getAllAnnonce($nbr_annonce, $user_id = null, $search = null) {

        try {            
            // Récupération des annonces pour un utilisateur
            $arrayAnnonce = $this->model
                ->with(['categories', 'abonnements', 'pictures']);

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
                    
                    if ($annonce->status === '0' ) {
                        $annonce['next_expiration_date'] = '...';
                    }else {
                        $annonce['next_expiration_date'] = $annonce->expiration_date;
                    }
    
                    if(isset($annonce)){
                        $annonce->abonnements->price_after_remise  = $annonce->abonnements->price - ( $annonce->abonnements->price * ($annonce->abonnements->remise/100) );
                        $annonce['url_image'] = $annonce->pictures->map(fn($p) => asset('storage/' . $p->location))->all();

                    }
                }
    
                return $arrayAnnonce;
            }
        } catch (\Exception $th) {
            Log::error('Erreur lors de l\'affichage des annonces: ' . $th->getMessage());

            return response()->json([
                'error'=>$th
            ]);
        }
    }


    function getAnnonce($annonce_id, $lang = null) {
        // Récupération des annonces pour un utilisateur
        $annonce = $this->model->with(['users', 'categories', 'abonnements', 'pictures'])->where('id', $annonce_id)->first();
        
        
        // Vérifiez si la collection est vide
        if (!empty($annonce)) {
                
                // Vérifier si cette date est dépassée
                if ($annonce->expiration_date === '0') {
                    // Si la date est dépassée, on met "expiré"
                    $annonce['next_expiration_date'] = 'expiré...';
                } else {
                    // Sinon, on retourne la date au format AAAA-MM-JJ
                    $annonce['next_expiration_date'] = $annonce->expiration_date;
                }

                $annonce['url_image'] = $annonce->pictures->map(fn($p) => asset('storage/' . $p->location))->all();

                if ($lang) { // envoyé les titles des catégories en anglais
                    foreach ($annonce->categories as $categorie) {
                            if($categorie->title_en){
                                $categorie->title = $categorie->title_en;
                            }
                    }
                }

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
        
        try {
            if ($user_id) {
                $annonce = $this->model->with('abonnements')->where('user_id', $user_id)->where('id', $annonce_id)->first();
            }
            else{
                $annonce = $this->model->where('id', $annonce_id)->first();
            }
            if(isset($annonce))
            {
                $type = $annonce->abonnements->type_time;
                $time = $annonce->abonnements->time;
                if($type === 'M')
                {
                    $time = $annonce->validity_period * 30;
                }
                elseif($type === 'A'){
                    $time = $annonce->validity_period * 365;
                }
                
                if($new_status === '3' && $annonce->status === '1' || $annonce->status === '0'){// Si c'est en cours et statut actuel est à 1:en_cours
                    $annonce->update([
                        'expiration_date' => now()->addDays($time)->format('Y-m-d'),
                    ]);
                }
    
                $annonce->update([
                    'status' => $new_status,
                    'updated_at' => now(),
                ]);
    
    
                return true;
            }
        } catch (\Exception $th) {
            Log::error('Erreur lors de la mise à jour du status : ' . $th->getMessage());
            return null;
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

    function getAllAnnonceFront($user_id = null, int $limit = 20)
    {
        $ordreExpr = "CASE
            WHEN user_id = ? THEN 0
            WHEN abonnement_id = 3 THEN 1
            WHEN abonnement_id = 2 THEN 2
            WHEN abonnement_id = 1 THEN 3
            ELSE 3
        END as ordre";

        $annonces = $this->model
            ->with(['users', 'categories', 'abonnements', 'pictures'])
            ->where('status', 3)
            ->where(function ($query) use ($user_id) {
                $query->where('user_id', $user_id)
                    ->orWhereHas('abonnements', function ($q) {
                        $q->where('hight_lite', 1)
                            ->whereIn('abonnements.id', [1, 2, 3]);
                    });
            })
            ->select('*')
            ->selectRaw($ordreExpr, [$user_id])
            ->orderBy('ordre')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();


        foreach ($annonces as $annonce) {
            $annonce->abonnements->price_after_remise  = $annonce->abonnements->price - ( $annonce->abonnements->price * ($annonce->abonnements->remise/100) );
            $annonce->url_image = $annonce->pictures->map(fn($p) => asset('storage/' . $p->location))->all();
        }
    
        return $annonces;
    }
    
    

    // Tous les annones à la une homepage
    function getAnnonceUne( $user_id = null ){

        $ordreExpr = "CASE 
            WHEN user_id = ? THEN 0 
            WHEN abonnement_id = 3 THEN 1 
            WHEN abonnement_id = 2 THEN 2 
            ELSE 3 
        END as ordre";

        $annonces = $this->model
            ->with(['users', 'categories', 'abonnements', 'pictures'])
            ->where('status', 3)
            ->where(function ($query) use ($user_id) {
                $query->where('user_id', $user_id)
                    ->orWhereHas('abonnements', function ($q) {
                        $q->where('hight_lite', 1)
                            ->whereIn('abonnements.id', [2,3]);
                    });
            })
            ->select('*')
            ->selectRaw($ordreExpr, [$user_id])
            ->orderByDesc('created_at')
            ->get();

        if ($annonces->isNotEmpty()) {
            foreach ($annonces as $key => $annonce) {
                $annonce->abonnements->price_after_remise  = $annonce->abonnements->price - ( $annonce->abonnements->price * ($annonce->abonnements->remise/100) );
                $annonce['url_image'] = $annonce->pictures->map(fn($p) => asset('storage/' . $p->location))->all();
            }

            return $annonces;
        }
    }

        
    function getTrieAnnonce($data)
    {
        try {
            $ordreExpr = "CASE
                WHEN user_id = ?        THEN 0
                WHEN abonnement_id = 3  THEN 1
                WHEN abonnement_id = 2  THEN 2
                WHEN abonnement_id = 1  THEN 3
                ELSE 4
            END AS ordre";

            $categ = $data['categ'];
            $lang = isset($data['lang']) ? $data['lang']: null;
            $country = isset($data['country']) ? $data['country'] : null;
            $city = isset($data['city']) ? $data['city'] : null;
            $user_id = isset($data['user_id']) ? $data['user_id'] : null;
            $amount_min = isset($data['amount_min']) ? $data['amount_min'] : null;
            $amount_max = isset($data['amount_max']) ? $data['amount_max'] : null;
            $asc = isset($data['asc']) ? $data['asc'] : null;

            $annonces = $this->model
                ->with(['users', 'categories', 'abonnements', 'pictures'])
                ->where('status', 3)

                ->when($categ, function ($q) use ($categ, $lang) {
                    if ($lang && $lang === 'en') {
                        $q->whereHas('categories', function ($c) use ($categ) {
                            $c->where('title_en', 'LIKE', "%$categ%");
                        });
                    } else {
                        $q->whereHas('categories', function ($c) use ($categ) {
                            $c->where('title', 'LIKE', "%$categ%");
                        });
                    }
                })
                ->when($country, fn ($q) =>
                    $q->where('country', 'LIKE', "%$country%"))
                ->when($amount_min, fn ($q) =>
                    $q->where('price', '>=', $amount_min))
                ->when($amount_max, fn ($q) =>
                    $q->where('price', '<=', $amount_max))
                ->when($city,    fn ($q) =>
                    $q->where('location', 'LIKE', "%$city%"))
    
                // ->when($user_id,
                //     fn ($q) => $q->where('user_id', $user_id),
                //     fn ($q) =>
                //         $q->whereHas('abonnements', fn ($qa) =>
                //             $qa->where('hight_lite', 1)
                //             ->whereIn('abonnements.id', [1, 2, 3])))
    
                ->select('*')
                ->selectRaw($ordreExpr, [$user_id])
                ->orderBy('ordre')
                ->when($asc, function ($q) {
                    $q->orderBy('created_at', 'asc');
                }, function ($q) {
                    $q->orderByDesc('created_at');
                })

                ->get();
    
            foreach ($annonces as $annonce) {

                if ($lang && $lang === 'en') { // envoyé les titles des catégories en anglais
                    foreach ($annonce->categories as $categorie) {
                            if($categorie->title_en){
                                $categorie->title = $categorie->title_en;
                            }
                    }
                }

                if ($ab = $annonce->abonnements) {
                    $ab->price_after_remise = $ab->price
                                            - ($ab->price * $ab->remise / 100);
                }
                $annonce->url_image = $annonce->pictures->map(fn($p) => asset('storage/' . $p->location))->all();
            }
        } catch (\Exception $th) {
            Log::error('Erreur lors du tri des annonces : ' . $th->getMessage());
            return null;
        }

        return $annonces;
    }


    //Envoie du mail apre paiement
    
    public function mailPaiment($email,$intitule,$amount, $mode_paiement, $typeAbonnement){
        try {
            // Envoyer l'email
            Mail::to($email)->send(new PaymentValidateMail($intitule,$amount,$mode_paiement, $typeAbonnement));
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du mail après paiement: ' . $e->getMessage());
            
            return false;
        }
    }
}
