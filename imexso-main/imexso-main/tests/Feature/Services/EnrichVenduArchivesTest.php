<?php

namespace Tests\Feature\Services;

use App\Models\Car;
use App\Models\CarPhoto;
use App\Services\CarXmlImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnrichVenduArchivesTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrich_attaches_photo_when_cdn_image_exists(): void
    {
        Http::fake([
            'https://photos.imexso.com/*' => Http::response('', 200),
        ]);

        $car = Car::factory()->sold()->create([
            'id' => 292445,
            'id_produit' => 'N292445',
            'make' => 'UNKNOWN',
            'model' => '—',
            'tags' => 'vendu-archive',
        ]);

        $attached = app(CarXmlImportService::class)->attachArchivePhotoIfAvailable($car);

        $this->assertTrue($attached);
        $this->assertDatabaseHas('car_photos', [
            'car_id' => $car->id,
            'url' => 'https://photos.imexso.com/photos_vehicules/N292445-1.jpg',
        ]);
    }

    public function test_enrich_skips_when_cdn_returns_not_found(): void
    {
        Http::fake([
            'https://photos.imexso.com/*' => Http::response('', 404),
        ]);

        $car = Car::factory()->sold()->create([
            'id' => 292446,
            'id_produit' => 'N292446',
            'make' => 'UNKNOWN',
            'tags' => 'vendu-archive',
        ]);

        $attached = app(CarXmlImportService::class)->attachArchivePhotoIfAvailable($car);

        $this->assertFalse($attached);
        $this->assertSame(0, CarPhoto::query()->where('car_id', $car->id)->count());
    }
}
