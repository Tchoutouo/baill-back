<?php
use App\Models\Annonce;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnonceFactory extends Factory
{
    protected $model = Annonce::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3), // Titre généré
            'subtitle' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 500, 10000), // Prix entre 500 et 10,000
            'contact' => $this->faker->phoneNumber,
            'country' => 'Cameroun',
            'neighborhood' => $this->faker->city,
            'is_published' => $this->faker->boolean,
           
            'status' => $this->faker->randomElement(['prof', 'student']),
            'is_forward' => $this->faker->boolean,
            'categorie' => json_encode([$this->faker->randomDigitNotNull]), // Catégorie aléatoire
            'abonnement_id' => 1, // Vous pouvez ajuster cela en fonction de vos données d'abonnement
            'user_id' => 3 // Associez à un utilisateur valide
        ];
    }
}

