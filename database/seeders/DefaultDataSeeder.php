<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Seeder;

class DefaultDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Default User',
            'username' => 'default',
            'email' => 'default@example.com',
            'password' => bcrypt('password'),
        ]);

        $locations = [
            ['name' => 'Karlsruhe Hauptbahnhof', 'type' => 'node', 'lat' => 48.9937, 'lon' => 8.4019],
            ['name' => 'Karlsruher Schloss', 'type' => 'node', 'lat' => 49.0135, 'lon' => 8.4044],
            ['name' => 'Marktplatz Karlsruhe', 'type' => 'node', 'lat' => 49.0094, 'lon' => 8.4044],
            ['name' => 'Zoologischer Stadtgarten Karlsruhe', 'type' => 'node', 'lat' => 48.9962, 'lon' => 8.3980],
            ['name' => 'KIT Campus Süd', 'type' => 'node', 'lat' => 49.0124, 'lon' => 8.4166],
        ];

        foreach ($locations as $location) {
            Location::create([
                'name' => $location['name'],
                'type' => $location['type'],
                'country_code' => 'DE',
                'timezone' => 'Europe/Berlin',
                'location' => Point::makeGeodetic($location['lat'], $location['lon']),
            ]);
        }
    }
}
