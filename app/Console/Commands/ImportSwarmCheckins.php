<?php

namespace App\Console\Commands;

use App\Repositories\LocationRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Services\OsmNameService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportSwarmCheckins extends Command
{
    protected $signature = 'app:import-swarm-checkins';
    protected $description = 'Import swarm checkins via KML file';

    private LocationRepository $locationRepository;
    private PostRepository $postRepository;
    private UserRepository $userRepository;

    public function __construct(?LocationRepository $locationRepository = null, ?PostRepository $postRepository = null, ?UserRepository $userRepository = null)
    {
        parent::__construct();
        $this->locationRepository = $locationRepository ?? new LocationRepository(new OsmNameService());
        $this->postRepository = $postRepository ?? new PostRepository();
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function handle()
    {
        $this->info('What is the path to the json file?');
        $path = $this->ask('Path to json file');
        $this->info('What is the username of the user?');
        $username = $this->ask('Username');

        if (str_starts_with($path, './')) {
            $path = substr($path, 2);
            $path = base_path($path);
        }

        file_get_contents($path);
        $user = $this->userRepository->getUserByUsername($username);
        if (!$user) {
            $this->error('User not found');
            return;
        }

        $this->info('Importing checkins for user: ' . $user->username);

        $this->info('User ID: ' . $user->id);

        $file = file_get_contents($path);
        $json = json_decode($file, false);
        if ($json === false) {
            $this->error('Error parsing json');
            return;
        }

        // start cli progress bar
        $this->output->progressStart(count($json));
        foreach ($json as $checkin) {
            $name = (string)$checkin->venue->name;
            $longitude = $checkin->venue->location->lng;
            $latitude = $checkin->venue->location->lat;

            $identifier = $checkin->venue->id;
            $body = $checkin->shout ?? null;
            $createdAt = $checkin->createdAt ? Carbon::createFromTimestamp($checkin->createdAt) : null;

            $categories = [];
            foreach ($checkin->venue->categories as $category) {
                $categories[] = $category->name;
            }


            // create location
            $location = $this->locationRepository->getOrCreateLocationByIdentifier($name, $latitude, $longitude, $identifier, 'venue', 'swarm');
            $location->tags()->updateOrCreate([
                'key' => 'swarm:category',
                'value' => implode(',', $categories),
            ]);

            // store location
            $this->postRepository->storeLocation($user, $location, $body, $createdAt);
            // advance progress bar
            $this->output->progressAdvance();
        }
    }
}
