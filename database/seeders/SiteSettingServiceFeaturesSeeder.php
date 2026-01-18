<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SiteSettingServiceFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'site_name' => 'PLOTTER.MD',
            ]
        );

        // Pregătește datele pentru actualizare
        $updateData = [];

        // Serviciu 1 - Rapiditate
        if (empty($settings->service_feature_1_icon)) {
            $updateData['service_feature_1_icon'] = 'Zap';
        }
        if (empty($settings->service_feature_1_title)) {
            $updateData['service_feature_1_title'] = 'RAPIDITATE';
        }
        if (empty($settings->service_feature_1_description)) {
            $updateData['service_feature_1_description'] = 'Efectuăm rapid comenzile dvs., asigurându-ne că fiecare detaliu este gestionat cu precizie și promptitudine.';
        }

        // Serviciu 2 - Suport în Alegere
        if (empty($settings->service_feature_2_icon)) {
            $updateData['service_feature_2_icon'] = 'Settings';
        }
        if (empty($settings->service_feature_2_title)) {
            $updateData['service_feature_2_title'] = 'SUPORT ÎN ALEGERE';
        }
        if (empty($settings->service_feature_2_description)) {
            $updateData['service_feature_2_description'] = 'Echipa noastră dedicată vă garantează că veți fi ajutați să faceți o alegere corectă pentru rezultatul dorit.';
        }

        // Serviciu 3 - Transport
        if (empty($settings->service_feature_3_icon)) {
            $updateData['service_feature_3_icon'] = 'Truck';
        }
        if (empty($settings->service_feature_3_title)) {
            $updateData['service_feature_3_title'] = 'TRANSPORT';
        }
        if (empty($settings->service_feature_3_description)) {
            $updateData['service_feature_3_description'] = 'Cu mândrie vă asigurăm că fiecare comandă beneficiază de un angajament ferm: garantăm livrare fără deteriorare';
        }

        // Serviciu 4 - Calitate Garantată
        if (empty($settings->service_feature_4_icon)) {
            $updateData['service_feature_4_icon'] = 'Smile';
        }
        if (empty($settings->service_feature_4_title)) {
            $updateData['service_feature_4_title'] = 'CALITATE GARANTATĂ';
        }
        if (empty($settings->service_feature_4_description)) {
            $updateData['service_feature_4_description'] = 'Suntem mândri să vă asigurăm că fiecare produs sau serviciu pe care îl oferim vine însoţit de o promisiune fermă: calitate garantată.';
        }

        // Actualizează doar dacă există date de actualizat
        if (!empty($updateData)) {
            $settings->update($updateData);
            
            // Șterge cache-ul pentru a forța reîncărcarea
            Cache::forget('site_settings');
            
            $this->command->info('Datele pentru servicii au fost populate cu succes!');
        } else {
            $this->command->info('Datele pentru servicii sunt deja setate. Nu s-au făcut modificări.');
        }
    }
}
