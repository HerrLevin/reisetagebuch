<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasUuids;

    protected $fillable = [
        'provider',
        'country',
        'name',
        'human_name',
        'spdx',
        'license_url',
        'source_url',
        'attribution_text',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * SPDX identifiers commonly seen in Transitous/GTFS license data, mapped to their
     * human-readable name and canonical license URL for display when the feed itself
     * doesn't provide a human_name/license_url.
     */
    public const array SPDX = [
        'ODbL-1.0' => ['name' => 'Open Database License (ODbL)', 'url' => 'https://spdx.org/licenses/ODbL-1.0.html'],
        'CC-BY-4.0' => ['name' => 'Creative Commons Attribution 4.0 International (CC BY 4.0)', 'url' => 'https://spdx.org/licenses/CC-BY-4.0.html'],
        'CC-BY-SA-4.0' => ['name' => 'Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)', 'url' => 'https://spdx.org/licenses/CC-BY-SA-4.0.html'],
        'CC-BY-SA-3.0' => ['name' => 'Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)', 'url' => 'https://spdx.org/licenses/CC-BY-SA-3.0.html'],
        'CC-BY-3.0' => ['name' => 'Creative Commons Attribution 3.0 Unported (CC BY 3.0)', 'url' => 'https://spdx.org/licenses/CC-BY-3.0.html'],
        'CC-BY-2.5' => ['name' => 'Creative Commons Attribution 2.5 Generic (CC BY 2.5)', 'url' => 'https://spdx.org/licenses/CC-BY-2.5.html'],
        'CC-BY-1.0' => ['name' => 'Creative Commons Attribution 1.0 Generic (CC BY 1.0)', 'url' => 'https://spdx.org/licenses/CC-BY-1.0.html'],
        'CC0-1.0' => ['name' => 'Creative Commons Zero v1.0 Universal (CC0-1.0)', 'url' => 'https://spdx.org/licenses/CC0-1.0.html'],
        'ODC-By-1.0' => ['name' => 'Open Data Commons Attribution License v1.0', 'url' => 'https://spdx.org/licenses/ODC-By-1.0.html'],
        'MIT' => ['name' => 'MIT License', 'url' => 'https://spdx.org/licenses/MIT.html'],
        'Unlicense' => ['name' => 'The Unlicense', 'url' => 'https://spdx.org/licenses/Unlicense.html'],
        'etalab-2.0' => ['name' => 'Etalab Open License 2.0', 'url' => 'https://spdx.org/licenses/etalab-2.0.html'],
        'NLOD-1.0' => ['name' => 'Norwegian Licence for Open Government Data (NLOD) 1.0', 'url' => 'https://spdx.org/licenses/NLOD-1.0.html'],
        'OGL-UK-3.0' => ['name' => 'Open Government License v3.0 (UK)', 'url' => 'https://spdx.org/licenses/OGL-UK-3.0.html'],
        'IODL-2.0' => ['name' => 'Italian Open Data Licence', 'url' => 'https://www.dati.gov.it/iodl/2.0'],
    ];

    public function transportTrips(): HasMany
    {
        return $this->hasMany(TransportTrip::class);
    }

    /**
     * Human-readable attribution line for this license, falling back to the known
     * SPDX name when the feed provided an identifier but no free-text attribution.
     */
    public function getAttributionText(): ?string
    {
        $name = $this->getDisplayName();

        if ($this->attribution_text) {
            return $name ? sprintf('%s (%s)', $this->attribution_text, $name) : $this->attribution_text;
        }

        if ($this->human_name && $name && $this->human_name !== $name) {
            return sprintf('Data provided by %s under %s', $this->human_name, $name);
        }

        $label = $this->human_name ?? $name;

        return $label ? sprintf('Data provided by %s', $label) : null;
    }

    public function getDisplayName(): ?string
    {
        return $this->getSpdxName() ?? $this->human_name;
    }

    public function getDisplayUrl(): ?string
    {
        return $this->license_url ?? self::SPDX[$this->spdx]['url'] ?? $this->source_url;
    }

    private function getSpdxName(): ?string
    {
        return self::SPDX[$this->spdx]['name'] ?? null;
    }
}
