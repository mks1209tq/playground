<?php

namespace Database\Seeders;

use App\Models\EmiratesId;
use Illuminate\Database\Seeder;

class EmiratesIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmiratesId::factory()->count(5)->create();
    }
}
