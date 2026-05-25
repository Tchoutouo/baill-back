<?php

namespace App\Repositories\Backend;
use App\Models\Categorie;
use App\Models\User;
use App\Repositories\ResourcesRepository;
use App\Http\Controllers\API\Backend\PictureController;
use Illuminate\Http\Request;

use function Laravel\Prompts\search;
use function PHPSTORM_META\type;

class CategorieRepository   extends ResourcesRepository
{

    private $pictureController;

    public function __construct(Categorie $categorie, PictureController $pictureController) {
        $this->model = $categorie;
        $this->pictureController = $pictureController;
    }

    public function getAll($lang = null) {
        $categories = $this->model->get();
        if ($lang && !empty($categories)) {
            foreach ($categories as $categorie) {
                    if($categorie->title_en){
                        $categorie->title = $categorie->title_en;
                    }
            }
            return $categories;
        }
        
        return $categories;
    }

    public function getById($id) {
        $categorie = $this->model->where('id', $id)->first();
        return $categorie;
    }

    /**created categorie */
    public function created($data = array()) {
        //defininir création de categorie
        
        $categorie = $this->model;
        $categorie->title= $data['title'];
        $categorie->description= $data['description'];
        if ($data['title_en']) {
            $categorie->title_en= $data['title_en'];
        }
        $categorie->save();
        // $array_sous = $data["array_sous"];

        // /** Remplir la table pivot categorie_sous_categories */
        // // dd(($array_sous));
        // if(isset($array_sous) && !empty($array_sous)){
        //       for ($i=0; $i < count($array_sous); $i++) { 
        //         $categorie->sousCategorie()->attach($array_sous[$i]);
        //       }
        // }
    
        return $categorie;
    }

    /**updated categorie */
    public function updated($data = array(), $id) {
        //defininir update de categorie
        $data["array_sous"] = [4];
        $categorie = $this->model->find($id);

        if(isset($categorie)){
            $categorie->title= $data['title'];
            $categorie->description= $data['description'];
            if ($data['title_en']) {
                $categorie->title_en= $data['title_en'];
            }
            $categorie->save();
            // $array_sous = $data["array_sous"];

            // /** Remplir la table pivot categorie_sous_categories */
            
            // // if(isset($array_sous) && !empty($array_sous)){
            // //       for ($i=0; $i < count($array_sous); $i++) { 
            // //         $categorie->sousCategorie()->sync($array_sous[$i]);
            // //       }
            // // }
        }

        return $categorie;
    }

    /**destroy categorie */
    public function destroy($id) {

        //definir destroy de categorie withCount:count notre d'occurrence dans la table pivot
        $categorie = $this->model->withCount('annonces')->find($id);

        // Si elle est associée à une annonce pas de suppression
        if($categorie->annonces_count > 0){
            return null;
        }
        $categorie = $categorie->delete();
        return true;
        
    }


    public function getAnnonceCateg($arrayCateg){
        // 1 requête pour toutes les catégories demandées avec eager loading
        $categories = $this->model
            ->with(['annonces' => function ($q) {
                $q->with(['pictures:id,annonce_id,location', 'users:id,username,email,country']);
            }])
            ->whereIn('id', $arrayCateg)
            ->get()
            ->keyBy('id');

        $annonces = collect();

        foreach ($arrayCateg as $categId) {
            $categorie = $categories->get($categId);
            if (!$categorie) continue;

            foreach ($categorie->annonces as $annonce) {
                $annonce['url_image'] = $annonce->pictures
                    ->map(fn($p) => asset('storage/' . $p->location))
                    ->all();
                $annonce['categorie'] = [$categorie->title];
                $annonces->push($annonce);
            }
        }

        return $annonces->values();
    }


    public function getAllCategories($nbre, $search = null, $lang = null){
        $allCateg = $this->model;
        if (isset($allCateg)) {

            if ($search) {
                if($lang && $lang === 'en'){
                    $allCateg = $allCateg->where(function ($q) use ($search) {
                        $q->where('title_en', 'LIKE', "%$search%");
                    });
                }else{
                    $allCateg = $allCateg->where(function ($q) use ($search) {
                        $q->where('title', 'LIKE', "%$search%");
                    });
                }
            }

            if($nbre){
                $allCateg = $allCateg->orderBy('created_at', 'desc')->paginate($nbre);
            }
            else{
                $allCateg = $allCateg->orderBy('created_at', 'desc')->get();
            }

            if ($lang && $lang === 'en') { // envoyé les titles des catégories en anglais
                foreach ($allCateg as $categorie) {
                    if($categorie->title_en){
                        $categorie->title = $categorie->title_en;
                    }
                }
            }
            return $allCateg;
        }
        return null;
    }


    /** Categories populaire */
    public function getCategPopulaire(){

        /** Pour chaque annonces associées à une categorie recuperer son id et user_id */
        $allCateg = $this->model->with(['annonces' => function ($query) {
            $query->select('annonces.id', 'annonces.user_id');
        }])->get();
        
        /**Le map parcours chaque élément de la collection allCateg et retourn une nouvelle collection
         * On bien faire un foreach à la place
         */
        if(isset($allCateg)){
            $result = $allCateg->map(function ($categorie) {
                $totalAnnonces = $categorie->annonces->count(); // Nombre d'annonces
                $uniqueUsers = $categorie->annonces->pluck('user_id')->unique()->count(); // Nombre d'utilisateurs uniques
                return  [
                    'categorie_id' => $categorie->id,
                    'nom' => $categorie->title,
                    'total_annonces' => $totalAnnonces,
                    'total_users' => $uniqueUsers,
                ];
            });
            return $result;
        }
        
        return "Aucune categorie populaire ...";
    }
}
