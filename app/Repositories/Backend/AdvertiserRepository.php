<?php

namespace App\Repositories\Backend;
use App\Models\User;
use App\Models\Profil;
use App\Repositories\ResourcesRepository;
use App\Repositories\Backend\AnnonceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordForgetMail;
use App\Mail\NewAdvertiserMail;
use Exception;
use Illuminate\Support\Facades\Auth;

class AdvertiserRepository   extends ResourcesRepository
{

    protected $annonceRepository;
    
    public function __construct(User $user, AnnonceRepository $annonceRepository) {
        $this->model = $user;
        $this->annonceRepository = $annonceRepository;
    }

    public function getById($id) {
        $user = $this->model->where('id', $id)->with('profils')->first();
        return $user;
    }

    /**created user et envoyé une notification à l'admin */
    public function created($data = array()) {
        try {

            $storeAdv = DB::transaction(function () use($data) {
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
                
                // $user_mail = "biocleanmoumbe@gmail.com";
                // $user_mail = "nomdecodeyvaltt@gmail.com";

                //Envoie du mail à l'admin
                // Mail::to(env('mail_username'))->send(new NewAdvertiserMail());
                // Mail::to($user_mail)->send(new NewAdvertiserMail());

                return $advertiser;
            });
            DB::commit();
    
        } catch (Exception $e) {
            DB::rollBack();
        }
        
        if(isset($storeAdv)){

            return $storeAdv;

        }else{

        }
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
        $advertiser->number= $data['number'];
        $advertiser->sex= $data['sex'];
        $advertiser->site_url= $data['site_url'];
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

    public function handleUpdate($id, $request){
        DB::beginTransaction();
        try {
            if (isset($request['picture'])) {
                $images = $request['picture'];
                $path = $images->store('images/users', 'public');
                $request['picture']= $path;
            }
            
            $this->update($id, $request);
            
            DB::commit();

            return true;

        } catch (\Exception $th) {

            DB::rollBack();
            
            return $th->getMessage() ;
        }

        
    }

    /**destroy user */
    public function destroy($id) {
        //defininir destroy de user
        
    }


    //**Listing des users */
    public function getAlluser($paginate , $search = null){

        $allAdv = $this->model->withCount("annonces")->where('profil_id',3);

        if($search){
            $allAdv = $allAdv->where(function($q) use ($search){
                $q->where("username","LIKE", "%$search%");
            });
        }
        $allAdv = $allAdv->orderBy('created_at', 'desc')
                        ->paginate($paginate);

        //**nbre annonces publiés */
        foreach($allAdv as $adv){
            $adv["nbre_publisher"] = $this->annonceRepository->getAnnoncePublisher($adv->id);
            $adv["nbre_expired"] = $this->annonceRepository->getAnnonceExpired($adv->id);
        }
        
        return $allAdv;
    }

    //** Change status */
    public function changeStatus($id){
        $advertiser = $this->model->find($id);

        if(isset($advertiser)){
            $advertiser->status= !$advertiser->status;
            $advertiser->save();
            return true;
        }
    }

    //** Change password */
    public function updatePassword($id, $data){

        $advertiser = $this->model->find($id);
        // $user = Auth::user();

        if(isset($advertiser)){
            if (Hash::check($data['password'],$advertiser->password)) {
                
                $advertiser->password = Hash::make($data['newpassword']);
                $advertiser->save();
                return true;
            }
            return false;
        }
    }

    //** Change password */
    public function forgetPassword($id, $data){

        $advertiser = $this->model->find($id);

        if(isset($advertiser)){

            if($data['email'] === $advertiser->email) {

                $newpassword = $this->PasswordRandom(8);

                $mail = $this->sendMail($newpassword,$data['email']);

                if ($mail === true) { //Si le mail a été bien envoyé
                    $advertiser->password = Hash::make($newpassword);
                    $advertiser->save();
                    return true;
                }else {//Sinon
                    return false;
                }
            }
        }
    }

    public function PasswordRandom($longueur) {
        return bin2hex(random_bytes($longueur / 2));
    }

    public function sendMail($newpassword,$email){
        try {
            // Envoyer l'email
            Mail::to($email)->send(new PasswordForgetMail($email,$newpassword));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
