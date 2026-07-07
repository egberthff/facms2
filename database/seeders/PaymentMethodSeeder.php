<?php

// namespace Database\Seeders;

// use App\Models\PaymentMethod;
// use Illuminate\Database\Seeder;

// class PaymentMethodSeeder extends Seeder
// {
//     public function run(): void
//     {
//         foreach (['GCash', 'Check', 'Bank Transfer'] as $method) {

//             PaymentMethod::updateOrCreate(
//                 ['name' => $method],
//                 [
//                     'is_active' => true
//                 ]
//             );
//         }
//     }
// }

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $ids = [
            '019f32cb-c172-72d3-97bd-b1b888fe3611',
            '019f32cb-c177-7193-a110-d5cc8aeb9c60',
            '019f32cb-c17d-7362-94af-09d027798fca'
        ];
        
        $counter = 0;
        
        foreach (['GCash', 'Check', 'Bank Transfer'] as $method) {

            // Find an existing record by ID, or instantiate a new model instance
            $paymentMethod = PaymentMethod::firstOrNew([
                'id' => $ids[$counter]
            ]);

            // Assign the values to be inserted or updated
            $paymentMethod->name = $method;
            $paymentMethod->is_active = true;
            
            // Save the record to the database
            $paymentMethod->save();
            
            $counter++;
        }
    }
}
