<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Campus;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $campuses = array(
            [
                'id' => '019f686b-6d0e-7027-98a0-c16ed47b318a',
                'campus_name' => 'Bohol Island State University - Main Campus',
                'acronym' => 'BISU-Main',
                'location' => "CPG North Avenue, Tagbilaran City",
                'is_active' => true,
            ],
            [
                'id' => '019f6865-edd5-730f-accc-33e9fab1a422',
                'campus_name' => 'Bohol Island State University - Calape Campus',
                'acronym' => 'BISU-Calape',
                'location' => "Calunasan, Calape",
                'is_active' => true,
            ],
            [
                'id' => '019f686b-6d17-713d-974e-eb7fca91c245',
                'campus_name' => 'Bohol Island State University - Balilihan Campus',
                'acronym' => 'BISU-Balilihan',
                'location' => "Magsija, Balilihan",
                'is_active' => true,
            ],
            [
                'id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a',
                'campus_name' => 'Bohol Island State University - Bilar Campus',
                'acronym' => 'BISU-Bilar',
                'location' => "Zamora, Bilar",
                'is_active' => true,
            ],
            [
                'id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451',
                'campus_name' => 'Bohol Island State University - Candijay Campus',
                'acronym' => 'BISU-Candijay',
                'location' => "Cogtong, Candijay",
                'is_active' => true,
            ],
            [
                'id' => '019f686b-6d20-711c-a538-433cb3f7b4f5',
                'campus_name' => 'Bohol Island State University - Clarin Campus',
                'acronym' => 'BISU-Clarin',
                'location' => "Poblacion, Clarin",
                'is_active' => true,
            ],
            [
                'id' => '019f686b-6d20-711c-b538-433cb3f7b789',
                'campus_name' => 'All Campus',
                'acronym' => 'AC',
                'location' => null,
                'is_active' => false,
            ]
        );

        foreach ($campuses as $campus){
            Campus::updateOrCreate(
                $campus
            );
        }
    }
}
