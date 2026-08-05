<?php

namespace Tests\Unit\Services;

use App\Models\LocationTag;
use App\Services\LocationEmojiService;
use PHPUnit\Framework\TestCase;

class LocationEmojiServiceTest extends TestCase
{
    public function test_get_emoji_from_tags_with_no_tags(): void
    {
        $service = new LocationEmojiService;

        $this->assertSame('📍', $service->getEmojiFromTags([]));
    }

    public function test_get_emoji_from_tags_with_known_category(): void
    {
        $service = new LocationEmojiService;
        $tags = $this->makeTags(['amenity' => 'restaurant']);

        $this->assertSame('🍽️', $service->getEmojiFromTags($tags));
    }

    public function test_get_emoji_from_tags_falls_back_to_category_default(): void
    {
        $service = new LocationEmojiService;
        $tags = $this->makeTags(['shop' => 'something_unmapped']);

        $this->assertSame('🛒', $service->getEmojiFromTags($tags));
    }

    public function test_get_emoji_from_tags_with_unknown_tags(): void
    {
        $service = new LocationEmojiService;
        $tags = $this->makeTags(['foo' => 'bar']);

        $this->assertSame('📍', $service->getEmojiFromTags($tags));
    }

    public function test_get_emoji_from_tags_prefers_specific_match_over_fallback(): void
    {
        $service = new LocationEmojiService;
        $tags = $this->makeTags([
            'shop' => 'bakery',
            'tourism' => 'hotel',
        ]);

        $this->assertSame('🥖', $service->getEmojiFromTags($tags));
    }

    /**
     * @return LocationTag[]
     */
    private function makeTags(array $keyValues): array
    {
        return array_map(
            fn ($key, $value) => new LocationTag(['key' => $key, 'value' => $value]),
            array_keys($keyValues),
            array_values($keyValues)
        );
    }
}
