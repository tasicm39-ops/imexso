<?php

namespace Tests\Feature\Services;

use App\Enums\CarHistoryStatus;
use App\Enums\CarStockStatus;
use App\Models\Car;
use App\Models\CarHistory;
use App\Services\CarXmlImportService;
use App\Services\VenduXmlImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarHistoryLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private const XML_CAR_ID = 999001;

    public function test_car_import_creates_history_then_vendu_adds_sold_with_buyer(): void
    {
        $this->importStockCar();

        $car = Car::query()->findOrFail(self::XML_CAR_ID);

        $this->assertSame(CarStockStatus::Available->value, $car->stock_status);
        $this->assertSame('active', $car->sync_status);
        $this->assertDatabaseHas('car_history', [
            'car_id' => self::XML_CAR_ID,
            'status' => CarHistoryStatus::Imported->value,
        ]);
        $this->assertDatabaseMissing('car_history', [
            'car_id' => self::XML_CAR_ID,
            'status' => CarHistoryStatus::Sold->value,
        ]);

        $stats = app(VenduXmlImportService::class)->import($this->buildVenduXml());

        $this->assertSame(1, $stats['processed']);
        $this->assertSame(1, $stats['updated']);
        $this->assertSame(1, $stats['history_created']);

        $car->refresh();

        $this->assertSame(CarStockStatus::Sold->value, $car->stock_status);
        $this->assertSame('sold', $car->sync_status);

        $soldHistory = CarHistory::query()
            ->where('car_id', self::XML_CAR_ID)
            ->where('status', CarHistoryStatus::Sold->value)
            ->first();

        $this->assertNotNull($soldHistory);
        $this->assertSame('C9999', $soldHistory->buyer_info['id_client'] ?? null);
        $this->assertSame('2026-05-01', $soldHistory->buyer_info['date_commande'] ?? null);

        $this->assertSame(2, CarHistory::query()->where('car_id', self::XML_CAR_ID)->count());
    }

    public function test_vendu_import_does_not_duplicate_sold_history(): void
    {
        $this->importStockCar();
        $service = app(VenduXmlImportService::class);

        $service->import($this->buildVenduXml());
        $secondRun = $service->import($this->buildVenduXml());

        $this->assertSame(0, $secondRun['history_created']);
        $this->assertSame(1, $secondRun['skipped']);
        $this->assertSame(2, CarHistory::query()->where('car_id', self::XML_CAR_ID)->count());
    }

    private function importStockCar(): void
    {
        app(CarXmlImportService::class)->import([
            'content' => $this->buildCarsXml(),
            'filename' => 'test-cars.xml',
            'user_id' => null,
        ]);
    }

    private function buildCarsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
<time>18/05/2026 12:00:00</time>
<total_items>1</total_items>
<item>
<count>1</count>
<id>999001</id>
<id_produit>N999001</id_produit>
<vin>VIN999001TEST00001</vin>
<marque><![CDATA[TEST]]></marque>
<modele><![CDATA[HISTORY]]></modele>
<finition><![CDATA[BASE]]></finition>
<carburant><![CDATA[ESSENCE]]></carburant>
<cylindre>1200</cylindre>
<puissance>100</puissance>
<moteur><![CDATA[ESS]]></moteur>
<poids>1200</poids>
<equipments_supplementaires>
<equipments_supplementaires_fr><equipment><![CDATA[test]]></equipment></equipments_supplementaires_fr>
</equipments_supplementaires>
<equipments_serie>
<equipments_serie_fr><equipment><![CDATA[abs]]></equipment></equipments_serie_fr>
</equipments_serie>
<annee_fabrication>2024</annee_fabrication>
<boite><![CDATA[MAN]]></boite>
<couleur><![CDATA[NOIR]]></couleur>
<km>100</km>
<co2></co2>
<co2wltp>120</co2wltp>
<wltp_erange></wltp_erange>
<norme_euro>EURO6</norme_euro>
<prix_pro>15000</prix_pro>
<tva>HTVA</tva>
<statut><![CDATA[En stock%%Auf Lager%%On Stock%%Stock]]></statut>
<date_immat>2024-01-01</date_immat>
<retention></retention>
<date_depart_garantie></date_depart_garantie>
<portes>5</portes>
<photos>
<photo>https://example.com/999001-1.jpg</photo>
</photos>
<categorie><![CDATA[Berline]]></categorie>
<prix_base_cat_fr_htva>10000</prix_base_cat_fr_htva>
<prix_tot_cat_fr_htva>12000</prix_tot_cat_fr_htva>
<prix_catalogue_fr_ttc>14000</prix_catalogue_fr_ttc>
<frais_vo>0</frais_vo>
<frais_vo_detail>NA</frais_vo_detail>
<vn_vo>VN</vn_vo>
<remise_france>0</remise_france>
<options_catalogue_fr></options_catalogue_fr>
<modele_catalogue_fr><![CDATA[TEST HISTORY]]></modele_catalogue_fr>
<destockage>NON</destockage>
<prix_argus></prix_argus>
<ancien_prix></ancien_prix>
<user_type>BE</user_type>
<new></new>
<remarque_catalogue><![CDATA[]]></remarque_catalogue>
<date_crea>2024-01-01</date_crea>
<prix_part_tvac>0</prix_part_tvac>
<codes_pub>SSL//02</codes_pub>
<tags><![CDATA[TEST]]></tags>
<exclude></exclude>
<code_boite>2</code_boite>
<code_couleur>1</code_couleur>
<equipments_principaux><equipment>1</equipment></equipments_principaux>
<carlab>0</carlab>
<carpass_url></carpass_url>
<malus>0</malus>
<etat></etat>
<auction><datetime_end>-0-0 0:0:0</datetime_end><min_price></min_price><prix_reserve></prix_reserve><description></description></auction>
<stock>1</stock>
<okcars>0</okcars>
<ecarlux>0</ecarlux>
<publish><platform>test</platform></publish>
</item>
</data>
XML;
    }

    private function buildVenduXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
<time>18/05/2026 13:00:00</time>
<total_items>1</total_items>
<item>
<id>999001</id>
<id_produit>O999001</id_produit>
<id_client>C9999</id_client>
<date_commande>2026-05-01</date_commande>
<statut>LIVRE</statut>
</item>
</data>
XML;
    }
}
