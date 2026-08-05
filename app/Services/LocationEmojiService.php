<?php

declare(strict_types=1);

namespace App\Services;

class LocationEmojiService
{
    private const string DEFAULT_EMOJI = '📍';

    /**
     * @var array<string, array<string, string>>
     */
    private const array CATEGORY_EMOJI = [
        'amenity' => [
            'restaurant' => '🍽️',
            'cafe' => '☕️',
            'bar' => '🍸',
            'pub' => '🍺',
            'fast_food' => '🍔',
            'ice_cream' => '🍦',
            'biergarten' => '🍻',
            'museum' => '🏛️',
            'theatre' => '🎭',
            'cinema' => '🎥',
            'nightclub' => '🪩',
            'arts_centre' => '🎨',
            'casino' => '🎰',
            'internet_cafe' => '💻',
            'public_bookcase' => '📚',
            'public_bath' => '🛁',
            'toilets' => '🚻',
            'waste_basket' => '🗑️',
            'waste_disposal' => '🚮',
            'vending_machine' => '🤖',
            'bench' => '🪑',
            'shelter' => '🏕️',
            'drinking_water' => '🚰',
            'fountain' => '⛲',
            'bbq' => '🍖',
            'shower' => '🚿',
            'bank' => '🏦',
            'atm' => '🏧',
            'bureau_de_change' => '💱',
            'pharmacy' => '💊',
            'hospital' => '🏥',
            'doctors' => '🩺',
            'clinic' => '🏨',
            'dentist' => '🦷',
            'veterinary' => '🐾',
            'post_box' => '📮',
            'post_office' => '🏤',
            'parcel_locker' => '📦',
            'telephone' => '☎️',
            'parking' => '🅿️',
            'fuel' => '⛽',
            'bicycle_parking' => '🚲',
            'bus_station' => '🚌',
            'bicycle_rental' => '🚴',
            'taxi' => '🚕',
            'charging_station' => '🔌',
            'car_rental' => '🚗',
            'parking_entrance' => '🅿️',
            'ferry_terminal' => '⛴️',
            'motorcycle_parking' => '🏍️',
            'bicycle_repair_station' => '🔧',
            'boat_rental' => '🚤',
            'police' => '👮',
            'townhall' => '🏛️',
            'fire_station' => '🚒',
            'social_facility' => '🏠',
            'courthouse' => '⚖️',
            'place_of_worship' => '⛪',
            'marketplace' => '🛍️',
            'car_wash' => '🧼',
            'vehicle_inspection' => '🔍',
            'driving_school' => '🚦',
            'nursing_home' => '🏡',
            'childcare' => '👶',
            'kindergarten' => '👶',
            'hunting_stand' => '🏹',
            'college' => '🎓',
            'car_sharing' => '🚗',
            'community_centre' => '🏢',
            'research_institute' => '🔬',
            'school' => '🏫',
            'music_venue' => '🎶',
        ],
        'shop' => [
            'supermarket' => '🛒',
            'bakery' => '🥖',
            'butcher' => '🥩',
            'coffee' => '☕',
            'convenience' => '🏪',
            'jewelry' => '💎',
            'chocolate' => '🍫',
            'books' => '📚',
            'tobacco' => '🚬',
            'chemist' => '💊',
            'clothes' => '👗',
            'cosmetics' => '💄',
            'fashion_accessories' => '👜',
            'ticket' => '🎫',
            'kiosk' => '📰',
            'hairdresser' => '✂️',
        ],
        'tourism' => [
            'artwork' => '🖼️',
            'community_centre' => '🏢',
            'library' => '📖',
            'gallery' => '🎨',
            'hotel' => '🏨',
            'attraction' => '🎡',
            'information' => 'ℹ️',
        ],
        'leisure' => [
            'outdoor_seating' => '🪑',
            'amusement_arcade' => '🕹️',
            'park' => '🌳',
            'playground' => '🛝',
            'sports_centre' => '🏋️',
            'fitness_centre' => '🏋️',
        ],
        'building' => [
            'school' => '🏫',
            'university' => '🎓',
            'kindergarten' => '👶',
        ],
        'healthcare' => [
            'hospital' => '🏥',
            'clinic' => '🏥',
            'doctors' => '🩺',
        ],
        'historic' => [
            'memorial' => '🕊️',
            'monument' => '🗿',
            'archeological_site' => '🏺',
            'wayside_shrine' => '⛩️',
            'castle' => '🏰',
        ],
        'highway' => [
            'bus_stop' => '🚌',
        ],
        'railway' => [
            'station' => '🚉',
            'subway_entrance' => '🚇',
            'tram_stop' => '🚊',
        ],
        'office' => [
            'lawyer' => '⚖️',
        ],
        'boundary' => [
            'administrative' => '🏛️',
            'national_park' => '🏞️',
            'protected_area' => '🛡️',
        ],
        'bridge' => [
            'yes' => '🌉',
        ],
        'natural' => [
            'water' => '🌊',
            'wood' => '🌲',
            'forest' => '🌳',
            'mountain' => '⛰️',
            'hill' => '⛰️',
            'peak' => '🏔️',
            'beach' => '🏖️',
            'glacier' => '🧊',
            'cave' => '🕳️',
            'wetland' => '🌾',
            'grassland' => '🌾',
            'heath' => '🌾',
            'moor' => '🌾',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const array FALLBACK_EMOJI = [
        'shop' => '🛒',
        'tourism' => '📸',
        'leisure' => '🌳',
        'building' => '🏬',
        'historic' => '🗽',
        'public_transport' => '🚏',
        'office' => '🧑‍💻',
        'boundary' => '🏛️',
    ];

    /**
     * @param  iterable<object{key: string, value: string}>  $tags
     */
    public function getEmojiFromTags(iterable $tags): string
    {
        $tags = is_array($tags) ? $tags : iterator_to_array($tags);

        foreach ($tags as $tag) {
            $emoji = $this->mapCategoryToEmoji($tag->key, $tag->value);
            if ($emoji !== self::DEFAULT_EMOJI) {
                return $emoji;
            }
        }

        foreach ($tags as $tag) {
            $emoji = $this->getFallbackEmoji($tag->key);
            if ($emoji !== self::DEFAULT_EMOJI) {
                return $emoji;
            }
        }

        return self::DEFAULT_EMOJI;
    }

    public function mapCategoryToEmoji(string $category, ?string $subcategory): string
    {
        if ($subcategory !== null && isset(self::CATEGORY_EMOJI[$category][$subcategory])) {
            return self::CATEGORY_EMOJI[$category][$subcategory];
        }

        return self::DEFAULT_EMOJI;
    }

    public function getFallbackEmoji(string $category): string
    {
        return self::FALLBACK_EMOJI[$category] ?? self::DEFAULT_EMOJI;
    }
}
