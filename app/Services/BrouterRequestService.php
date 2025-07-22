<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BrouterProfileCreationFailed;
use App\Exceptions\BrouterRouteCreationFailed;
use Clickbar\Magellan\Data\Geometries\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;

class BrouterRequestService
{
    private const string API_URL = 'https://brouter.de/brouter';

    private string $tempProfile;

    private VersionService $versionService;

    private Client $client;

    /**
     * @throws BrouterProfileCreationFailed
     * @throws ConnectionException
     */
    public function __construct(?VersionService $versionService = null)
    {
        $this->versionService = $versionService ?? new VersionService;
    }

    private function initClient(): void
    {
        $this->client = new Client([
            'base_uri' => self::API_URL,
            'cookies' => true,
            'headers' => [
                'User-Agent' => $this->versionService->getUserAgent(),
            ],
        ]);
    }

    private function getClient(): Client
    {
        if (! isset($this->client)) {
            $this->initClient();
        }

        return $this->client;
    }

    /**
     * @throws BrouterProfileCreationFailed
     * @throws GuzzleException
     */
    private function getTempProfile(): ?string
    {
        if (isset($this->tempProfile)) {
            return $this->tempProfile;
        }

        // get file from storage
        $filePath = storage_path('app/brouter/rail_improved.brf');

        $fileContent = file_get_contents($filePath);

        $time = round(microtime(true) * 1000);
        $response = $this->getClient()->post(self::API_URL.'/profile/custom_'.$time, ['body' => $fileContent]);

        if ($response->getStatusCode() !== 200) {
            throw new BrouterProfileCreationFailed('Failed to create temporary BRouter profile: '.$response->getBody());
        }
        $json = json_decode($response->getBody()->getContents(), true);
        $this->tempProfile = $json['profileid'];

        return $this->tempProfile;
    }

    /**
     * @throws BrouterRouteCreationFailed
     * @throws GuzzleException
     */
    public function getRoute(Point $start, Point $stop, string $pathType): string
    {
        if ($pathType === 'rail') {
            try {
                $profile = $this->getTempProfile();
            } catch (BrouterProfileCreationFailed|ConnectionException $e) {
                report($e);
                $profile = 'rail';
            }
        } elseif ($pathType === 'road') {
            $profile = 'car-fast';
        } else {
            throw new BrouterRouteCreationFailed('Unsupported path type: '.$pathType);
        }

        $url = sprintf(
            '%s?format=geojson&alternativeidx=0&profile=%s&lonlats=%f,%f|%f,%f',
            self::API_URL,
            $profile,
            $start->getLongitude(),
            $start->getLatitude(),
            $stop->getLongitude(),
            $stop->getLatitude()
        );
        $response = $this->getClient()->get($url);

        if ($response->getStatusCode() !== 200) {
            throw new BrouterRouteCreationFailed('Failed to get route from BRouter: '.$response->getBody());
        }

        $json = json_decode($response->getBody()->getContents(), true);
        if (isset($json['error'])) {
            throw new BrouterRouteCreationFailed('BRouter returned an error: '.$json['error']);
        }
        if (empty($json['features'])) {
            throw new BrouterRouteCreationFailed('BRouter returned no route features.');
        }

        return json_encode($json['features'][0]['geometry']);
    }
}
