<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TrAddressPtt\Models\City;
use TrAddressPtt\Models\District;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = config('traddressptt.default_json_path');
        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);

        $total = 0;
        foreach ($data as $cityData) {
            $total += count($cityData['districts']);
        }

        $this->command->info("Seeding districts...");
        $this->command->getOutput()->progressStart($total);

        $districtCount = 0;
        try {
            DB::transaction(function () use ($data, &$districtCount) {
                foreach ($data as $cityData) {
                    $city = City::firstOrCreate(['name' => $cityData['name']]);
                    foreach ($cityData['districts'] as $districtData) {
                        District::create([
                            'city_id' => $city->id,
                            'name' => $districtData['name'],
                        ]);
                        $districtCount++;
                    }
                }
            });
            $this->command->getOutput()->progressFinish();
            $this->command->info("Districts seeding completed! Total: $districtCount");
        } catch (\Throwable $e) {
            $this->command->error('An error occurred during district seeding: ' . $e->getMessage());
        }
    }
} 