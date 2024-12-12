<?php

namespace App\Repositories\Backend;
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
        
        $user = $this->model->where('profil_id', 3)->count();
        
        return $user;
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
        $user->profil_id= "2";
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
        $user->profil_id= "2";
        $user->save();

        return $user;
    }

    /**destroy user */
    public function destroy($id) {
        //defininir destroy de user
    }

    /**Listing des utilisateurs bloqués */
    public function getUserLock(){
        $userlock = $this->model->where('status',0)->where('profil_id', 3)->get();

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
