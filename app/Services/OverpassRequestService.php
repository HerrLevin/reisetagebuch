<?php

namespace App\Services;

use App\Exceptions\OverpassApiOverloaded;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

abstract class OverpassRequestService
{
    protected Client $client;

    protected VersionService $versionService;

    public function __construct(?Client $client = null, ?VersionService $versionService = null)
    {
        $this->versionService = $versionService ?? new VersionService;
        $this->client = $client ?? new Client;
    }

    /**
     * @throws OverpassApiOverloaded
     */
    protected function request(string $query): array
    {
        Log::debug('Overpass query: '.$query);

        try {
            $response = $this->client->request(
                'POST',
                config('app.overpass.url'),
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => $this->versionService->getUserAgent(),
                        'Accept-Encoding' => 'gzip, deflate',
                    ],
                    'form_params' => ['data' => $query],
                ]
            );
        } catch (GuzzleException $e) {
            Log::debug('Overpass GuzzleException: '.$e->getMessage());
            if ($e->getCode() === 504) {
                throw new OverpassApiOverloaded;
            }

            return [];
        }
        $response = $response->getBody()->getContents();

        return json_decode($response, true) ?? [];
    }

    /**
     * @throws OverpassApiOverloaded
     */
    public function getById(string $id, string $type = 'node'): array
    {
        if (! in_array($type, ['node', 'way', 'relation'])) {
            throw new InvalidArgumentException('Invalid type: '.$type);
        }

        $query = sprintf('[out:json];%s(id:%s);out;', $type, $id);

        return $this->request($query);
    }
}
