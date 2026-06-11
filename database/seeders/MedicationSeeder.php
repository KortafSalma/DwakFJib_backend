<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\Pharmacy;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacies = Pharmacy::all();

        if ($pharmacies->isEmpty()) {
            Pharmacy::factory(3)->create();
            $pharmacies = Pharmacy::all();
        }

        $medications = [
            ['name' => 'Doliprane 500mg', 'generic_name' => 'Paracetamol', 'category' => 'Antalgique', 'price' => 25.00, 'stock' => 500, 'requires_prescription' => false],
            ['name' => 'Efferalgan 1g', 'generic_name' => 'Paracetamol', 'category' => 'Antalgique', 'price' => 35.00, 'stock' => 400, 'requires_prescription' => false],
            ['name' => 'Dafalgan 500mg', 'generic_name' => 'Paracetamol', 'category' => 'Antalgique', 'price' => 28.00, 'stock' => 350, 'requires_prescription' => false],
            ['name' => 'Advil 400mg', 'generic_name' => 'Ibuprofene', 'category' => 'Anti-inflammatoire', 'price' => 32.00, 'stock' => 300, 'requires_prescription' => false],
            ['name' => 'Nifluril 250mg', 'generic_name' => 'Acide niflumique', 'category' => 'Anti-inflammatoire', 'price' => 45.00, 'stock' => 150, 'requires_prescription' => true],
            ['name' => 'Bi-Profenid 150mg', 'generic_name' => 'Ketoprofene', 'category' => 'Anti-inflammatoire', 'price' => 38.00, 'stock' => 200, 'requires_prescription' => true],
            ['name' => 'Voltarene 50mg', 'generic_name' => 'Diclofenac', 'category' => 'Anti-inflammatoire', 'price' => 22.00, 'stock' => 280, 'requires_prescription' => false],
            ['name' => 'Augmentin 1g', 'generic_name' => 'Amoxicilline + Acide clavulanique', 'category' => 'Antibiotique', 'price' => 65.00, 'stock' => 180, 'requires_prescription' => true],
            ['name' => 'Clamoxyl 500mg', 'generic_name' => 'Amoxicilline', 'category' => 'Antibiotique', 'price' => 42.00, 'stock' => 250, 'requires_prescription' => true],
            ['name' => 'Mopral 20mg', 'generic_name' => 'Omeprazole', 'category' => 'Gastro-enterologie', 'price' => 55.00, 'stock' => 160, 'requires_prescription' => true],
            ['name' => 'Inipomp 20mg', 'generic_name' => 'Omeprazole', 'category' => 'Gastro-enterologie', 'price' => 48.00, 'stock' => 190, 'requires_prescription' => true],
            ['name' => 'Spasfon 80mg', 'generic_name' => 'Phloroglucinol', 'category' => 'Antispasmodique', 'price' => 18.00, 'stock' => 420, 'requires_prescription' => false],
            ['name' => 'Debridat 100mg', 'generic_name' => 'Trimebutine', 'category' => 'Antispasmodique', 'price' => 25.00, 'stock' => 280, 'requires_prescription' => false],
            ['name' => 'Smecta 3g', 'generic_name' => 'Diosmectite', 'category' => 'Anti-diarrheique', 'price' => 15.00, 'stock' => 350, 'requires_prescription' => false],
            ['name' => 'Glucophage 850mg', 'generic_name' => 'Metformine', 'category' => 'Diabete', 'price' => 40.00, 'stock' => 300, 'requires_prescription' => true],
            ['name' => 'Daonil 5mg', 'generic_name' => 'Glibenclamide', 'category' => 'Diabete', 'price' => 22.00, 'stock' => 200, 'requires_prescription' => true],
            ['name' => 'Amlor 5mg', 'generic_name' => 'Amlodipine', 'category' => 'Cardiologie', 'price' => 60.00, 'stock' => 180, 'requires_prescription' => true],
            ['name' => 'Lopril 20mg', 'generic_name' => 'Captopril', 'category' => 'Cardiologie', 'price' => 35.00, 'stock' => 220, 'requires_prescription' => true],
            ['name' => 'Lasilix 40mg', 'generic_name' => 'Furosemide', 'category' => 'Cardiologie', 'price' => 20.00, 'stock' => 260, 'requires_prescription' => true],
            ['name' => 'Daflon 500mg', 'generic_name' => 'Diosmine', 'category' => 'Veinotonique', 'price' => 48.00, 'stock' => 190, 'requires_prescription' => false],
            ['name' => 'Ventoline 100µg', 'generic_name' => 'Salbutamol', 'category' => 'Respiratoire', 'price' => 45.00, 'stock' => 140, 'requires_prescription' => true],
            ['name' => 'Seretide 250µg', 'generic_name' => 'Fluticasone + Salmeterol', 'category' => 'Respiratoire', 'price' => 120.00, 'stock' => 80, 'requires_prescription' => true],
            ['name' => 'Zyrtecset 10mg', 'generic_name' => 'Cetirizine', 'category' => 'Antihistaminique', 'price' => 22.00, 'stock' => 380, 'requires_prescription' => false],
            ['name' => 'Polaramine 2mg', 'generic_name' => 'Dexchlorpheniramine', 'category' => 'Antihistaminique', 'price' => 15.00, 'stock' => 300, 'requires_prescription' => false],
            ['name' => 'Rhinurex', 'generic_name' => 'Pseudoephedrine', 'category' => 'ORL', 'price' => 28.00, 'stock' => 250, 'requires_prescription' => false],
            ['name' => 'Tardyferon 80mg', 'generic_name' => 'Fer sulfate', 'category' => 'Supplement', 'price' => 30.00, 'stock' => 350, 'requires_prescription' => false],
            ['name' => 'Piros 20mg', 'generic_name' => 'Piroxicam', 'category' => 'Anti-inflammatoire', 'price' => 18.00, 'stock' => 220, 'requires_prescription' => true],
            ['name' => 'Dulcolax 5mg', 'generic_name' => 'Bisacodyl', 'category' => 'Laxatif', 'price' => 12.00, 'stock' => 400, 'requires_prescription' => false],
        ];

        foreach ($pharmacies as $pharmacy) {
            foreach ($medications as $med) {
                Medication::factory()->create([
                    'pharmacy_id' => $pharmacy->id,
                    'name' => $med['name'],
                    'generic_name' => $med['generic_name'],
                    'category' => $med['category'],
                    'price' => $med['price'],
                    'stock' => $med['stock'],
                    'requires_prescription' => $med['requires_prescription'],
                ]);
            }
        }
    }
}
