<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;

class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        $pharmaciesData = [
            ['name' => 'Dr. Fatima Zahra El Ouazzani', 'email' => 'fatima.zahra@pharmacie.ma', 'pharmacy_name' => 'Pharmacie El Farah', 'address' => '45 Boulevard Mohammed V, Casablanca', 'city' => 'Casablanca', 'lat' => 33.5731, 'lng' => -7.5898, 'phone' => '+212522456789'],
            ['name' => 'Dr. Karim Benlemlih', 'email' => 'karim.benlemlih@pharmacie.ma', 'pharmacy_name' => 'Pharmacie Ibn Sina', 'address' => '12 Avenue Hassan II, Rabat', 'city' => 'Rabat', 'lat' => 34.0209, 'lng' => -6.8416, 'phone' => '+212537123456'],
            ['name' => 'Dr. Nadia Berrada', 'email' => 'nadia.berrada@pharmacie.ma', 'pharmacy_name' => 'Pharmacie Al Amal', 'address' => '78 Rue de la Liberte, Marrakech', 'city' => 'Marrakech', 'lat' => 31.6295, 'lng' => -7.9811, 'phone' => '+212524987654'],
            ['name' => 'Dr. Hassan Mokhtari', 'email' => 'hassan.mokhtari@pharmacie.ma', 'pharmacy_name' => 'Pharmacie Atlas', 'address' => '33 Boulevard Zerktouni, Fes', 'city' => 'Fes', 'lat' => 34.0331, 'lng' => -4.9998, 'phone' => '+212535765432'],
            ['name' => 'Dr. Salma El Ouafi', 'email' => 'salma.ouafi@pharmacie.ma', 'pharmacy_name' => 'Pharmacie Al Karama', 'address' => '56 Avenue Pasteur, Tanger', 'city' => 'Tanger', 'lat' => 35.7673, 'lng' => -5.7998, 'phone' => '+212539345678'],
            ['name' => 'Dr. Youssef El Fassi', 'email' => 'youssef.fassi@pharmacie.ma', 'pharmacy_name' => 'Pharmacie Ennakhil', 'address' => '90 Avenue Mohammed VI, Agadir', 'city' => 'Agadir', 'lat' => 30.4278, 'lng' => -9.5981, 'phone' => '+212528112233'],
        ];

        foreach ($pharmaciesData as $data) {
            $user = User::factory()->create([
                'role' => User::ROLE_PHARMACY,
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            Pharmacy::factory()->create([
                'user_id' => $user->id,
                'name' => $data['pharmacy_name'],
                'address' => $data['address'],
                'city' => $data['city'],
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'phone' => $data['phone'],
                'is_verified' => true,
            ]);
        }

        Pharmacy::factory(6)->create();
    }
}
