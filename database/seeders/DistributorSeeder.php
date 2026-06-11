<?php

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    public function run(): void
    {
        $distributorsData = [
            ['name' => 'Karim El Idrissi', 'email' => 'karim.idrissi@distripharma.ma', 'company' => 'DistriPharma Maroc', 'phone' => '+212522889900', 'address' => 'Zone Industrielle Sidi Bernoussi, Casablanca', 'city' => 'Casablanca'],
            ['name' => 'Mohamed Bennis', 'email' => 'mohamed.bennis@medirex.ma', 'company' => 'Medirex Maroc', 'phone' => '+212537556677', 'address' => 'Parc Industriel Technopark, Rabat', 'city' => 'Rabat'],
            ['name' => 'Hicham Ouazzani', 'email' => 'hicham.ouazzani@sothema.ma', 'company' => 'Sothema Distribution', 'phone' => '+212524334455', 'address' => 'Zone Industrielle Sidi Ghanem, Marrakech', 'city' => 'Marrakech'],
            ['name' => 'Rachid El Fassi', 'email' => 'rachid.fassi@cooperpharma.ma', 'company' => 'Cooper Pharma Logistics', 'phone' => '+212535667788', 'address' => 'Route d\'Immouzzer, Fes', 'city' => 'Fes'],
            ['name' => 'Youssef Ait Ali', 'email' => 'y.aitali@maghrebpharma.ma', 'company' => 'Maghreb Pharma Distribution', 'phone' => '+212539223344', 'address' => 'Zone Franche Tanger Med, Tanger', 'city' => 'Tanger'],
        ];

        foreach ($distributorsData as $data) {
            $user = User::factory()->create([
                'role' => User::ROLE_DISTRIBUTOR,
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            Distributor::factory()->create([
                'user_id' => $user->id,
                'name' => $data['company'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
            ]);
        }

        Distributor::factory(3)->create();
    }
}
