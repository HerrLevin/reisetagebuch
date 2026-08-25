<?php

namespace Tests\Feature\Services;

use App\Services\GpxParserService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GpxParserServiceTest extends TestCase
{
    public function test_parses_valid_gpx(): void
    {
        $gpx = <<<'GPX'
        <?xml version="1.0" encoding="UTF-8"?>
        <gpx version="1.1"><trk><trkseg>
            <trkpt lat="52.5" lon="13.4"></trkpt>
            <trkpt lat="52.6" lon="13.5"></trkpt>
        </trkseg></trk></gpx>
        GPX;

        $lineString = (new GpxParserService)->parse($gpx);

        $this->assertCount(2, $lineString->getPoints());
    }

    public function test_throws_422_for_malformed_xml(): void
    {
        try {
            (new GpxParserService)->parse('this is not xml at all <<<');
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }

    public function test_throws_422_for_gpx_with_too_few_points(): void
    {
        $gpx = <<<'GPX'
        <?xml version="1.0" encoding="UTF-8"?>
        <gpx version="1.1"><trk><trkseg>
            <trkpt lat="52.5" lon="13.4"></trkpt>
        </trkseg></trk></gpx>
        GPX;

        try {
            (new GpxParserService)->parse($gpx);
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }
}
