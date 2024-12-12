<?php

namespace App\Repositories\Backend;
use App\Models\User;
use App\Models\Profil;
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
    

}
