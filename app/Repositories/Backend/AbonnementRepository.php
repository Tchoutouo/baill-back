<?php

namespace App\Repositories\Backend;
use App\Models\Abonnement;
use App\Repositories\ResourcesRepository;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $abonnement->type_time = $this->typeTime($abonnement->type_time,$abonnement->time);
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

    /**Get all abonnement */
    public function getAllAbonnement($nbre_page, $search) {

        $user = Auth::user();
        //* Pour chaque abonnement compter le nombre d'utilisateur associer dans la table annonces
        $abonnement = $this->model->with('annonces')
                    ->select('abonnements.*', DB::raw('(
                        SELECT COUNT(DISTINCT annonces.user_id)
                        FROM annonces
                        WHERE annonces.abonnement_id = abonnements.id
                    ) as total_users'));
        
        if($user->profil_id === 3){
            if($user->qte_free == 0 ) {
                $abonnement = $abonnement->where('id','<>',1);
            }
        }

        if($search){
            $abonnement = $abonnement->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }

        if($nbre_page){
            $abonnement = $abonnement->orderBy('created_at', 'desc')->paginate($nbre_page);
        }
        else{
            $abonnement = $abonnement->orderBy('created_at', 'desc')->get();
        }

        return $abonnement;
    }

    /**Progress Abonnement */
    public function progressAbonnement() {

        //* Pour chaque abonnement compter le nombre d'utilisateur associer dans la table annonces
        $abonnement = $this->model
                    ->select('abonnements.*', DB::raw('(
                        SELECT COUNT(*)
                        FROM annonces
                        WHERE annonces.abonnement_id = abonnements.id
                        AND annonces.status = 3
                    ) as total_annonces'),
                    DB::raw('(
                        SELECT COUNT(*)
                        FROM annonces
                        WHERE annonces.status = 3
                    ) as total_publier'))
                    ->get();

        /** Calcul des progressions et durée*/
        foreach ($abonnement as $abon) {

            if($abon->total_publier){
                $abon->progress = ($abon->total_annonces * 100) / $abon->total_publier ;
            }else{
                $abon->progress = 0;
            }

            $abon->type_time = $this->typeTime($abon->type_time,$abon->time) ;

        }

        return $abonnement;
    }

    public function typeTime($timeType, $time){

        if($timeType == "H"){
            $timeType  = $time / 24 ." Heure(s)";
        }

        if($timeType == "S"){
            $timeType  = $time / 7 ." Semaine(s)";
        }

        if($timeType  == "M"){
            $timeType  = $time / 30 ." Mois";
        }

        if($timeType  == "A"){
            $timeType  = $time / 365 ." Année(s)";
        }

        return $timeType;
    }


    public function changeStatusAbonnement($id){
        $abonnement = $this->model->find($id);

        if(isset($abonnement)){
            $abonnement->is_actived= !$abonnement->is_actived;
            $abonnement->save();
            return true;
        }
    }

}
