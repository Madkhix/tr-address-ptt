<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TrAddressPtt\Models\City;
use TrAddressPtt\Models\District;
use TrAddressPtt\Models\Subdistrict;
use Illuminate\Support\Facades\DB;

class SubdistrictSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = config('traddressptt.default_json_path');
        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);

        $subdistrictNames = [];
        foreach ($data as $cityData) {
            foreach ($cityData['districts'] as $districtData) {
                foreach ($districtData['neighborhoods'] as $neighborhoodData) {
                    $parts = array_map('trim', explode('/', $neighborhoodData['name']));
                    $subdistrictName = $parts[1] ?? null;
                    if ($subdistrictName) {
                        $subdistrictNames[$districtData['name'] . '|' . $subdistrictName] = [
                            'district_name' => $districtData['name'],
                            'subdistrict_name' => $subdistrictName,
                        ];
                    }
                }
            }
        }
        $total = count($subdistrictNames);
        $this->command->info("Seeding subdistricts...");
        $this->command->getOutput()->progressStart($total);

        $subdistrictCount = 0;
        try {
            DB::transaction(function () use ($subdistrictNames, &$subdistrictCount) {
                foreach ($subdistrictNames as $key => $info) {
                    $district = \TrAddressPtt\Models\District::where('name', $info['district_name'])->first();
                    if ($district) {
                        \TrAddressPtt\Models\Subdistrict::firstOrCreate([
                            'district_id' => $district->id,
                            'name' => $info['subdistrict_name'],
                        ]);
                        $subdistrictCount++;
                    }
                }
            });
            $this->command->getOutput()->progressFinish();
            $this->command->info("Subdistricts seeding completed! Total: $subdistrictCount");
        } catch (\Throwable $e) {
            $this->command->error('An error occurred during subdistrict seeding: ' . $e->getMessage());
        }
    }
} 