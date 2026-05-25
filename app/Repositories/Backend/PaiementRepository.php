<?php

namespace App\Repositories\Backend;
use App\Models\Paiement;
use App\Repositories\ResourcesRepository;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Exception;

class PaiementRepository   extends ResourcesRepository
{

    public function __construct(Paiement $paiement) {
        $this->model = $paiement;
    }

    public function getAll() {
        $paiement = $this->model->all();
        return $paiement;
    }

    public function getById($id) {
        $paiement = $this->model->where('id', $id)->first();
        return $paiement;
    }

    public function check_account($id){
        $paiement = $this->model->where('id', $id)->first()->price;
        return $paiement;
    }


    /**save paiement */
    public function created($data) {
        try {
            //code...
            $paiement = $this->model;
            $paiement->mode_paiement = $data["mode_paiement"];
            $paiement->montant = $data["amount"];
            $paiement->date_paiement = Carbon::now()->format('Y-m-d');
            $paiement->number = $data["number"];
            $paiement->user_id = $data["user_id"];
            $paiement->abonnement_id = $data["abonnement_id"];
            if (isset($data["reference"])) {
                $paiement->reference = $data["reference"];
            }
            $paiement->save();
            
            return $paiement;
        } catch (Exception $e) {
            \Log::error('Erreur lors de la création du paiement methode created: ' . $e->getMessage());
        }
    }

    /** Chiffre d'affaires */
    public function ChiffreAffaire(){
        return $this->model->where('statut', 2)->sum('montant');
    }

    /** Chiffre d'affaires ce mois-ci */
    public function revenueThisMonth(){
        return $this->model
            ->where('statut', 2)
            ->whereYear('date_paiement', Carbon::now()->year)
            ->whereMonth('date_paiement', Carbon::now()->month)
            ->sum('montant');
    }

    /** Chiffre d'affaires le mois dernier */
    public function revenueLastMonth(){
        return $this->model
            ->where('statut', 2)
            ->whereYear('date_paiement', Carbon::now()->subMonth()->year)
            ->whereMonth('date_paiement', Carbon::now()->subMonth()->month)
            ->sum('montant');
    }

    /** Revenus par mode de paiement */
    public function revenueByMode(){
        return $this->model
            ->where('statut', 2)
            ->select('mode_paiement', DB::raw('SUM(montant) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('mode_paiement')
            ->get();
    }

    /** Tendance mensuelle des revenus sur N mois — 1 requête groupBy au lieu de N */
    public function monthlyRevenueTrend($months = 6){
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $rows = $this->model
            ->where('statut', 2)
            ->where('date_paiement', '>=', $start)
            ->selectRaw('YEAR(date_paiement) as yr, MONTH(date_paiement) as mo, SUM(montant) as total')
            ->groupByRaw('YEAR(date_paiement), MONTH(date_paiement)')
            ->get()
            ->keyBy(fn($r) => sprintf('%04d-%02d', $r->yr, $r->mo));

        $results = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key  = $date->format('Y-m');
            $results[] = [
                'month' => $key,
                'label' => $date->locale('fr')->isoFormat('MMM Y'),
                'total' => (float) ($rows[$key]->total ?? 0),
            ];
        }
        return $results;
    }

    /** Paiements récents */
    public function recentPayments($limit = 5){
        return $this->model
            ->where('statut', 2)
            ->with(['user:id,username,email', 'abonnement:id,name'])
            ->orderBy('date_paiement', 'desc')
            ->limit($limit)
            ->get(['id', 'mode_paiement', 'montant', 'date_paiement', 'user_id', 'abonnement_id']);
    }

    public function updated($id,$reference,$statut){
        $paiement = $this->model->where('id', $id)->first();
        $paiement->statut = $statut;
        if($reference){
            $paiement->reference = $reference;
        }
        $paiement->save();
        return true;
    }

}
