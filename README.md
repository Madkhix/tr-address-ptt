# tr-address-ptt

A Laravel package for Turkey's provinces, districts, subdistricts (quarters), neighborhoods, and postal codes. Easily import, query, and keep up-to-date address data from PTT's official source.

## Installation

```bash
composer require madkhix/tr-address-ptt
```

## Publishing Migrations, Seeders, and Config

```bash
php artisan vendor:publish --provider="TrAddressPtt\TrAddressServiceProvider" --tag="migrations"
php artisan migrate
php artisan vendor:publish --provider="TrAddressPtt\TrAddressServiceProvider" --tag="seeders"
php artisan vendor:publish --provider="TrAddressPtt\TrAddressServiceProvider" --tag="traddressptt-config"
```

> After running the Python scraper, copy the generated `tr-address-ptt-data.json` file to your Laravel project root (where the `artisan` file is located). You can also use the following artisan command to copy it automatically:
>
> ```bash
> php artisan traddress-ptt:publish-json
> ```

> You can change the JSON data file path in `config/traddressptt.php` if needed.

## Migration Structure

Each table has its own migration file:
- `cities`
- `districts`
- `subdistricts`
- `neighborhoods`
- `postcodes`

You can run all migrations at once:

```bash
php artisan migrate
```

Or migrate a specific table (advanced usage):

```bash
php artisan migrate --path=src/database/migrations/2024_01_03_000000_create_subdistricts_table.php
```

## Seeder Structure

Each table has its own seeder:
- `CitySeeder`
- `DistrictSeeder`
- `SubdistrictSeeder` (**independent, only seeds subdistricts**)
- `NeighborhoodSeeder` (**does not create subdistricts, only links to them**)
- `PostcodeSeeder`
- `TrAddressSeeder` (runs all in order)

Seed all data:

```bash
php artisan db:seed --class=Database\Seeders\TrAddressPttSeeder
```

Or seed a specific table:

```bash
php artisan db:seed --class=Database\Seeders\SubdistrictSeeder
php artisan db:seed --class=Database\Seeders\NeighborhoodSeeder
```

> **Note:**
> - Run `SubdistrictSeeder` before `NeighborhoodSeeder` if you seed them separately.
> - `NeighborhoodSeeder` will not create subdistricts, only link to existing ones.

## Usage

```php
use TrAddressPtt\Models\City;
use TrAddressPtt\Models\Subdistrict;
use TrAddressPtt\Models\Neighborhood;

$cities = City::all();
$subdistricts = Subdistrict::where('district_id', $districtId)->get();
$neighborhoods = Neighborhood::where('subdistrict_id', $subdistrictId)->get();
```

## Data Structure

The package uses a normalized structure:
- `subdistricts` table for quarters (semt), linked to districts
- `neighborhoods` table links to subdistricts via `subdistrict_id`

If your JSON data contains entries like:

```json
{
  "name": "BEYAZEVLER MAH / MAHFESIĞMAZ / 01170"
}
```

- `name` → "BEYAZEVLER MAH"
- `subdistrict` → "MAHFESIĞMAZ" (stored in subdistricts table)
- `postcode` → "01170"

The seeders will automatically parse and store these fields in the correct tables and columns.

## Updating Address Data (Fetching from PTT)

This package does **not** include the Python data fetcher script by default. To update the address data from the official PTT source, use the separate Python script available at:

https://github.com/madkhix/tr-address-fetcher-ptt

**Usage:**

1. Clone or download the script from the repository above.
2. Run the script in your terminal:
   ```bash
   python fetch_tr_address_data.py
   ```
3. The script will generate a `tr-address-ptt-data.json` file.
4. Copy this file to your Laravel project root (where `artisan` is located).
5. Import the data using the package's artisan command:
   ```bash
   php artisan traddress-ptt:import-ptt tr-address-ptt-data.json
   ```

## License
MIT 