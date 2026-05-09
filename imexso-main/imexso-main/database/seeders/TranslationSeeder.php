<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    /**
     * @var array<string, array<string, array<string, string>>>
     */
    private array $translations = [
        'ui' => [
            'nav.home' => ['en' => 'Home', 'fr' => 'Accueil'],
            'nav.inventory' => ['en' => 'Inventory', 'fr' => 'Inventaire'],
            'nav.contact' => ['en' => 'Contact', 'fr' => 'Contact'],
            'nav.login' => ['en' => 'Login', 'fr' => 'Connexion'],
            'nav.register' => ['en' => 'Register', 'fr' => 'Inscription'],
            'nav.logout' => ['en' => 'Logout', 'fr' => 'Déconnexion'],
            'nav.profile' => ['en' => 'Profile', 'fr' => 'Profil'],
            'general.search' => ['en' => 'Search', 'fr' => 'Rechercher'],
            'general.loading' => ['en' => 'Loading...', 'fr' => 'Chargement...'],
            'general.save' => ['en' => 'Save', 'fr' => 'Enregistrer'],
            'general.cancel' => ['en' => 'Cancel', 'fr' => 'Annuler'],
            'general.back' => ['en' => 'Back', 'fr' => 'Retour'],
            'general.next' => ['en' => 'Next', 'fr' => 'Suivant'],
            'general.previous' => ['en' => 'Previous', 'fr' => 'Précédent'],
            'general.showing_results' => ['en' => 'Showing results', 'fr' => 'Résultats affichés'],
            'general.no_results' => ['en' => 'No results found', 'fr' => 'Aucun résultat trouvé'],
            'auth.login' => ['en' => 'Login', 'fr' => 'Connexion'],
            'auth.register' => ['en' => 'Register', 'fr' => 'Inscription'],
            'auth.email' => ['en' => 'Email', 'fr' => 'E-mail'],
            'auth.password' => ['en' => 'Password', 'fr' => 'Mot de passe'],
            'auth.forgot_password' => ['en' => 'Forgot your password?', 'fr' => 'Mot de passe oublié ?'],
            'auth.reset_password' => ['en' => 'Reset Password', 'fr' => 'Réinitialiser le mot de passe'],
            'auth.pending_approval' => ['en' => 'Pending Approval', 'fr' => 'En attente d\'approbation'],
            'auth.pending_approval_message' => ['en' => 'Your account is pending approval. Please wait for an administrator to validate your account.', 'fr' => 'Votre compte est en attente d\'approbation. Veuillez attendre qu\'un administrateur valide votre compte.'],
        ],
        'filters' => [
            'filter.make' => ['en' => 'Make', 'fr' => 'Marque'],
            'filter.model' => ['en' => 'Model', 'fr' => 'Modèle'],
            'filter.fuel_type' => ['en' => 'Fuel Type', 'fr' => 'Carburant'],
            'filter.gearbox' => ['en' => 'Gearbox', 'fr' => 'Boîte de vitesses'],
            'filter.color' => ['en' => 'Color', 'fr' => 'Couleur'],
            'filter.condition' => ['en' => 'Condition', 'fr' => 'État'],
            'filter.horsepower' => ['en' => 'Horsepower', 'fr' => 'Puissance'],
            'filter.price' => ['en' => 'Price', 'fr' => 'Prix'],
            'filter.mileage' => ['en' => 'Mileage', 'fr' => 'Kilométrage'],
            'filter.year' => ['en' => 'Year', 'fr' => 'Année'],
            'filter.search' => ['en' => 'Search', 'fr' => 'Rechercher'],
            'filter.sort_by' => ['en' => 'Sort by', 'fr' => 'Trier par'],
            'filter.reset' => ['en' => 'Reset', 'fr' => 'Réinitialiser'],
            'filter.apply' => ['en' => 'Apply', 'fr' => 'Appliquer'],
        ],
        'car' => [
            'car.mileage' => ['en' => 'Mileage', 'fr' => 'Kilométrage'],
            'car.fuel_type' => ['en' => 'Fuel Type', 'fr' => 'Carburant'],
            'car.gearbox' => ['en' => 'Gearbox', 'fr' => 'Boîte de vitesses'],
            'car.horsepower' => ['en' => 'Horsepower', 'fr' => 'Puissance'],
            'car.color' => ['en' => 'Color', 'fr' => 'Couleur'],
            'car.year' => ['en' => 'Year', 'fr' => 'Année'],
            'car.price' => ['en' => 'Price', 'fr' => 'Prix'],
            'car.condition' => ['en' => 'Condition', 'fr' => 'État'],
            'car.new' => ['en' => 'New', 'fr' => 'Neuf'],
            'car.used' => ['en' => 'Used', 'fr' => 'Occasion'],
            'car.doors' => ['en' => 'Doors', 'fr' => 'Portes'],
            'car.engine' => ['en' => 'Engine', 'fr' => 'Moteur'],
            'car.co2' => ['en' => 'CO2 Emissions', 'fr' => 'Émissions CO2'],
            'car.vin' => ['en' => 'VIN', 'fr' => 'NIV'],
            'car.overview' => ['en' => 'Overview', 'fr' => 'Aperçu'],
            'car.equipment' => ['en' => 'Equipment', 'fr' => 'Équipement'],
            'car.options' => ['en' => 'Options', 'fr' => 'Options'],
            'car.photos' => ['en' => 'Photos', 'fr' => 'Photos'],
            'car.related' => ['en' => 'Related Vehicles', 'fr' => 'Véhicules similaires'],
            'car.view_details' => ['en' => 'View Details', 'fr' => 'Voir les détails'],
        ],
    ];

    public function run(): void
    {
        $rows = [];

        foreach ($this->translations as $group => $keys) {
            foreach ($keys as $key => $locales) {
                foreach ($locales as $locale => $value) {
                    $rows[] = [
                        'group' => $group,
                        'key' => $key,
                        'locale' => $locale,
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        Translation::query()->upsert($rows, ['group', 'key', 'locale'], ['value']);
    }
}
