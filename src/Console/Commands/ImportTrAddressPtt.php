<?php

namespace TrAddressPtt\Console\Commands;

use Illuminate\Console\Command;
use TrAddressPtt\Models\City;
use TrAddressPtt\Models\District;
use TrAddressPtt\Models\Neighborhood;
use TrAddressPtt\Models\Postcode;
use Illuminate\Support\Facades\DB;

class ImportTrAddressPtt extends Command
{
    protected $signature = 'traddress-ptt:import-ptt {json_path}';
    protected $description = 'Import TRAddress JSON address data into the database.';

    public function handle()
    {
        $jsonPath = $this->argument('json_path');
        if (!file_exists($jsonPath)) {
            $this->error("File not found: $jsonPath");
            return 1;
        }
        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);
        if (!$data) {
            $this->error('JSON could not be read or is invalid.');
            return 1;
        }
        $this->info('Starting data import...');

        $cityCount = 0;
        $districtCount = 0;
        $neighborhoodCount = 0;
        $postcodeCount = 0;

        try {
            DB::transaction(function () use ($data, &$cityCount, &$districtCount, &$neighborhoodCount, &$postcodeCount) {
                foreach ($data as $cityData) {
                    $city = City::create(['name' => $cityData['name']]);
                    $cityCount++;
                    foreach ($cityData['districts'] as $districtData) {
                        $district = District::create([
                            'city_id' => $city->id,
                            'name' => $districtData['name'],
                        ]);
                        $districtCount++;
                        foreach ($districtData['neighborhoods'] as $neighborhoodData) {
                            $neighborhood = Neighborhood::create([
                                'district_id' => $district->id,
                                'name' => $neighborhoodData['name'],
                            ]);
                            $neighborhoodCount++;
                            foreach ($neighborhoodData['postcodes'] as $code) {
                                Postcode::create([
                                    'neighborhood_id' => $neighborhood->id,
                                    'code' => $code,
                                ]);
                                $postcodeCount++;
                            }
                        }
                    }
                }
            });
            $this->info('Data import completed!');
            $this->info("Total imported: $cityCount cities, $districtCount districts, $neighborhoodCount neighborhoods, $postcodeCount postcodes.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('An error occurred during import: ' . $e->getMessage());
            return 1;
        }
    }
} 