<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TrAddressPtt\Models\City;
use TrAddressPtt\Models\District;
use TrAddressPtt\Models\Neighborhood;
use TrAddressPtt\Models\Subdistrict;
use Illuminate\Support\Facades\DB;

class NeighborhoodSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = config('traddressptt.default_json_path');
        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);

        $total = 0;
        foreach ($data as $cityData) {
            foreach ($cityData['districts'] as $districtData) {
                $total += count($districtData['neighborhoods']);
            }
        }

        $this->command->info("Seeding neighborhoods...");
        $this->command->getOutput()->progressStart($total);

        $neighborhoodCount = 0;
        try {
            DB::transaction(function () use ($data, &$neighborhoodCount, $total, $cityData, $districtData) {
                foreach ($data as $cityData) {
                    $city = City::firstOrCreate(['name' => $cityData['name']]);
                    foreach ($cityData['districts'] as $districtData) {
                        $district = District::firstOrCreate([
                            'city_id' => $city->id,
                            'name' => $districtData['name'],
                        ]);
                        foreach ($districtData['neighborhoods'] as $neighborhoodData) {
                            $parts = array_map('trim', explode('/', $neighborhoodData['name']));
                            $neighborhoodName = isset($parts[0]) ? trim($parts[0]) : null;
                            $subdistrictName = isset($parts[1]) ? trim($parts[1]) : null;
                            $subdistrict = null;
                            if ($subdistrictName) {
                                $normalizedSubdistrictName = mb_strtolower($subdistrictName);
                                $subdistrict = \TrAddressPtt\Models\Subdistrict::where('district_id', $district->id)
                                    ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedSubdistrictName])
                                    ->first();
                                if (!$subdistrict) {
                                    $this->command->warn("Subdistrict not found: '" . $subdistrictName . "' (District: $district->name)");
                                }
                            }
                            \TrAddressPtt\Models\Neighborhood::create([
                                'district_id' => $district->id,
                                'subdistrict_id' => $subdistrict ? $subdistrict->id : null,
                                'name' => $neighborhoodName,
                            ]);
                            $neighborhoodCount++;
                        }
                    }
                }
            });
            $this->command->getOutput()->progressFinish();
            $this->command->info("Neighborhoods seeding completed! Total: $neighborhoodCount");
        } catch (\Throwable $e) {
            $this->command->error('An error occurred during neighborhood seeding: ' . $e->getMessage());
        }
    }
} 