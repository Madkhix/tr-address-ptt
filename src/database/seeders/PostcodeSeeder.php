<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TrAddressPtt\Models\City;
use TrAddressPtt\Models\District;
use TrAddressPtt\Models\Neighborhood;
use TrAddressPtt\Models\Postcode;
use TrAddressPtt\Models\Subdistrict;
use Illuminate\Support\Facades\DB;

class PostcodeSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = config('traddressptt.default_json_path');
        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);

        $total = 0;
        foreach ($data as $cityData) {
            foreach ($cityData['districts'] as $districtData) {
                foreach ($districtData['neighborhoods'] as $neighborhoodData) {
                    $total += count($neighborhoodData['postcodes']);
                }
            }
        }

        $this->command->info("Seeding postcodes...");
        $this->command->getOutput()->progressStart($total);

        $postcodeCount = 0;
        try {
            DB::transaction(function () use ($data, &$postcodeCount) {
                foreach ($data as $cityData) {
                    $city = City::firstOrCreate(['name' => $cityData['name']]);
                    foreach ($cityData['districts'] as $districtData) {
                        $district = District::firstOrCreate([
                            'city_id' => $city->id,
                            'name' => $districtData['name'],
                        ]);
                        foreach ($districtData['neighborhoods'] as $neighborhoodData) {
                            $parts = array_map('trim', explode('/', $neighborhoodData['name']));
                            $neighborhoodName = $parts[0] ?? null;
                            $subdistrictName = $parts[1] ?? null;
                            $subdistrict = null;
                            if ($subdistrictName) {
                                $subdistrict = Subdistrict::firstOrCreate([
                                    'district_id' => $district->id,
                                    'name' => $subdistrictName,
                                ]);
                            }
                            $neighborhood = Neighborhood::firstOrCreate([
                                'district_id' => $district->id,
                                'subdistrict_id' => $subdistrict ? $subdistrict->id : null,
                                'name' => $neighborhoodName,
                            ]);
                            foreach ($neighborhoodData['postcodes'] as $code) {
                                Postcode::create([
                                    'neighborhood_id' => $neighborhood->id,
                                    'code' => $code,
                                ]);
                                $postcodeCount++;
                                $this->command->getOutput()->progressAdvance();
                            }
                        }
                    }
                }
            });
            $this->command->getOutput()->progressFinish();
            $this->command->info("Postcodes seeding completed! Total: $postcodeCount");
        } catch (\Throwable $e) {
            $this->command->error('An error occurred during postcode seeding: ' . $e->getMessage());
        }
    }
} 