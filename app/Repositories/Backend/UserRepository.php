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
    }

    /**updated user */
    public function updated($data = array(), $id) {
        //defininir update de user
    }

    /**destroy user */
    public function destroy($id) {
        //defininir destroy de user
    }

}
