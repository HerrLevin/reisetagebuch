<?php

namespace App\Services;

use App\Exceptions\OverpassApiOverloaded;
use InvalidArgumentException;

class OverpassLocationRequestService extends OverpassRequestService
{
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
