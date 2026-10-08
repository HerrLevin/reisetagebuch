<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\ActivityPub\Extensions\RtbExtension;
use App\Dto\ActivityPub\Extensions\RtbExtensionFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Defensively parses the optional `rtbExtension` reisetagebuch-to-reisetagebuch envelope
 * on an incoming ActivityPub Note. The input is untrusted JSON from an arbitrary remote
 * server; RtbExtensionFactory and the Rtb*Data DTOs it dispatches to do the actual
 * field-by-field validation, this class only guarantees that no exception from that
 * process ever escapes into ordinary Note ingestion.
 */
class ActivityPubExtensionParser
{
    public function parse(array $object): ?RtbExtension
    {
        try {
            $data = $object['rtbExtension'] ?? null;
            $extension = RtbExtensionFactory::fromArray($data);

            if (! $extension) {
                Log::debug('No valid RTB extension found in incoming Note', ['data' => $data, 'object' => $object]);
            }

            return $extension;
        } catch (Throwable $e) {
            Log::warning('Failed to parse RTB extension, ignoring it', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
