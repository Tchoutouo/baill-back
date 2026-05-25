<?php

namespace App\Repositories\Backend;
use App\Enums\ProfilCode;
use App\Models\User;
use Carbon\Carbon;
use App\Repositories\ResourcesRepository;
use Illuminate\Http\Request;

class UserRepository   extends ResourcesRepository
{

    public function __construct(User $user) {
        $this->model = $user;
    }

    public function getById($id) {
        $user = $this->model->where('id', $id)->with('profils')->first();
        return $user;
    }

    public function getAllUsers() {
        return $this->model->count();
    }

    /** Nouveaux utilisateurs ce mois-ci */
    public function newUsersThisMonth(){
        return $this->model
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
    }

    /** Nouveaux utilisateurs le mois dernier */
    public function newUsersLastMonth(){
        return $this->model
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();
    }

    /** Répartition par profil */
    public function usersByProfile(){
        return $this->model
            ->select('profil_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('profil_id')
            ->with('profils:id,name')
            ->get();
    }

    /** Tendance d'inscriptions mensuelle sur N mois */
    public function userGrowthTrend($months = 6){
        $results = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = $this->model
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $results[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->locale('fr')->isoFormat('MMM Y'),
                'count' => $count,
            ];
        }
        return $results;
    }

    /** Inscriptions récentes */
    public function recentUsers($limit = 5){
        return $this->model
            ->with('profils:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'username', 'email', 'country', 'city', 'status', 'profil_id', 'created_at']);
    }

    /** Top pays par nombre d'annonceurs */
    public function usersByCountry($limit = 5){
        return $this->model
            ->where('profil_id', ProfilCode::Advertiser->value)
            ->select('country', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    
    /**created user */
    public function created($data = array()) {
        //defininir création de user
        $user = $this->model;
        
        $user->name= $data['username'];
        $user->username= $data['username'];
        $user->last_name= $data['last_name'];
        $user->first_name= $data['last_name'];
        $user->email= $data['email'];
        $user->whatsapp_number= $data['whatsapp_number'];
        $user->country= $data['country'];
        $user->city= $data['city'];
        $user->neighborhood= $data['neighborhood'];
        $user->password= $data['password'];
        $user->profil_id= ProfilCode::Admin->value;
        $user->save();

        return $user;
    }

    /**updated user */
    public function updated($data = array(), $id) {
        //defininir update de user
        $user = $this->model->find($id);
        
        $user->name= $data['username'];
        $user->username= $data['username'];
        $user->last_name= $data['last_name'];
        $user->first_name= $data['last_name'];
        $user->email= $data['email'];
        $user->whatsapp_number= $data['whatsapp_number'];
        $user->country= $data['country'];
        $user->city= $data['city'];
        $user->neighborhood= $data['neighborhood'];
        $user->password= $data['password'];
        $user->profil_id= ProfilCode::Admin->value;
        $user->save();

        return $user;
    }


    
    /**Decrementer le nombre free de cette utitilisateur */
    public function decrementFree($id)
    {
        $user = $this->model->find($id);

        if($user->qte_free > 0){
            $user->qte_free = $user->qte_free - 1 ;
            $user->save();
            return $user->qte_free;
        }
        return null;
    }

    /**destroy user */
    public function destroy($id) {
        //defininir destroy de user
    }

    /**Listing des utilisateurs bloqués */
    public function getUserLock(){
        $userlock = $this->model->where('status', 0)->where('profil_id', ProfilCode::Advertiser->value)->get();

        if (isset($userlock)) {

            $taUserblock = [];
            foreach ($userlock as $user) {
                $user->date_update = $this->tempsPasse($user->date_update);
                $taUserblock[]= [
                    "id" => $user->id,
                    "username" => $user->username,
                    "time_lock" => $user->date_update
                ];
            }

            return $taUserblock;
        }
        return "Aucun utilisateur bloqué...";
    }


    /** Temps passé */
    public function tempsPasse($date)
    {
        // Convertir la date en instance de Carbon
        $dateDonnee = Carbon::parse($date);
        $dateActuelle = Carbon::now();

        // Calculer la différence
        $differenceEnSecondes = $dateDonnee->diffInSeconds($dateActuelle);

        // Vérifier les unités de temps dans l'ordre décroissant
        if ($differenceEnSecondes >= 31536000) { // 1 an
            $annees = $dateDonnee->diffInYears($dateActuelle);
            return 'Il y\'a '.$annees . ' ' . ($annees > 1 ? 'années' : 'année');
        }
        if ($differenceEnSecondes >= 2592000) { // 1 mois
            $mois = $dateDonnee->diffInMonths($dateActuelle);
            return 'Il y\'a '.$mois . ' ' . ($mois > 1 ? 'mois' : 'mois');
        }
        if ($differenceEnSecondes >= 86400) { // 1 jour
            $jours = $dateDonnee->diffInDays($dateActuelle);
            return 'Il y\'a '.$jours . ' ' . ($jours > 1 ? 'jours' : 'jour');
        }
        if ($differenceEnSecondes >= 3600) { // 1 heure
            $heures = $dateDonnee->diffInHours($dateActuelle);
            return 'Il y\'a '.$heures . ' ' . ($heures > 1 ? 'heures' : 'heure');
        }
        if ($differenceEnSecondes >= 60) { // 1 minute
            $minutes = $dateDonnee->diffInMinutes($dateActuelle);
            return 'Il y\'a '.$minutes . ' ' . ($minutes > 1 ? 'minutes' : 'minute');
        }
        return 'Quelques secondes';
    }

}
