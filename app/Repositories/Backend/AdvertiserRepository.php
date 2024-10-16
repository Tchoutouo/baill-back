<?php

namespace App\Repositories\Backend;
use App\Models\User;
use App\Models\Profil;
use App\Repositories\ResourcesRepository;
use Illuminate\Http\Request;

class AdvertiserRepository   extends ResourcesRepository
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
        $advertiser = $this->model;
        
        $advertiser->name= $data['username'];
        $advertiser->username= $data['username'];
        $advertiser->last_name= $data['last_name'];
        $advertiser->first_name= $data['first_name'];
        $advertiser->email= $data['email'];
        $advertiser->whatsapp_number= $data['whatsapp_number'];
        $advertiser->country= $data['country'];
        $advertiser->city= $data['city'];
        $advertiser->neighborhood= $data['neighborhood'];
        $advertiser->password= $data['password'];
        $advertiser->profil_id= 3;
        $advertiser->save();

        return $advertiser;
    }

    /**updated user */
    public function updated($data = array(), $id, Request $request) {
        //defininir update de user

        $advertiser = $this->model->find($id);
        
        $advertiser->name= $data['username'];
        $advertiser->username= $data['username'];
        $advertiser->last_name= $data['last_name'];
        $advertiser->first_name= $data['first_name'];
        $advertiser->email= $data['email'];
        $advertiser->whatsapp_number= $data['whatsapp_number'];
        $advertiser->country= $data['country'];
        $advertiser->city= $data['city'];
        $advertiser->neighborhood= $data['neighborhood'];
        $advertiser->password= $data['password'];
        if(isset($data['cni'])){
            $advertiser->cni= $data['cni'];
        }
        if ($request->hasFile('picture')) {
                $images = $request->file('picture');
                $path = $images->store('images', 'public');
                $advertiser->picture= $path;
        }
        $advertiser->profil_id= "3";
        $advertiser->save();

        return $advertiser;
    }

    /**destroy user */
    public function destroy($id) {
        //defininir destroy de user
        
    }

}
