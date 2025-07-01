<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TrAddressPtt\Models\City;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run()
    {
        $jsonPath = config('traddressptt.default_json_path');
        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);

        $this->command->info("Seeding cities...");
        $this->command->getOutput()->progressStart(count($data));

        $cityCount = 0;
        try {
            DB::transaction(function () use ($data, &$cityCount) {
                foreach ($data as $cityData) {
                    City::create(['name' => $cityData['name']]);
                    $cityCount++;
                }
            });
            $this->command->getOutput()->progressFinish();
            $this->command->info("Cities seeding completed! Total: $cityCount");
        } catch (\Throwable $e) {
            $this->command->error('An error occurred during city seeding: ' . $e->getMessage());
        }
    }
} 