<?php

/**
 * Manual E2E check: stock import → IMPORTED history → vendu → SOLD + buyer.
 *
 * Usage: php scripts/test-car-history-lifecycle.php
 */

use App\Models\Car;
use App\Models\CarHistory;
use App\Services\CarXmlImportService;
use App\Services\VenduXmlImportService;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$xmlCarId = 999002;

$carsXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data><time>test</time><total_items>1</total_items>
<item>
<id>{$xmlCarId}</id>
<id_produit>N{$xmlCarId}</id_produit>
<vin>VIN{$xmlCarId}TEST00001</vin>
<marque><![CDATA[TEST]]></marque>
<modele><![CDATA[LIFECYCLE]]></modele>
<finition><![CDATA[BASE]]></finition>
<carburant><![CDATA[ESSENCE]]></carburant>
<cylindre>1200</cylindre>
<puissance>100</puissance>
<moteur><![CDATA[ESS]]></moteur>
<poids>1200</poids>
<equipments_supplementaires><equipments_supplementaires_fr><equipment><![CDATA[x]]></equipment></equipments_supplementaires_fr></equipments_supplementaires>
<equipments_serie><equipments_serie_fr><equipment><![CDATA[abs]]></equipment></equipments_serie_fr></equipments_serie>
<annee_fabrication>2024</annee_fabrication>
<boite><![CDATA[MAN]]></boite>
<couleur><![CDATA[NOIR]]></couleur>
<km>100</km>
<co2></co2><co2wltp>120</co2wltp><wltp_erange></wltp_erange><norme_euro>EURO6</norme_euro>
<prix_pro>15000</prix_pro><tva>HTVA</tva>
<statut><![CDATA[En stock%%Auf Lager%%On Stock%%Stock]]></statut>
<date_immat>2024-01-01</date_immat><retention></retention><date_depart_garantie></date_depart_garantie>
<portes>5</portes><photos><photo>https://example.com/test.jpg</photo></photos>
<categorie><![CDATA[Berline]]></categorie>
<prix_base_cat_fr_htva>10000</prix_base_cat_fr_htva><prix_tot_cat_fr_htva>12000</prix_tot_cat_fr_htva>
<prix_catalogue_fr_ttc>14000</prix_catalogue_fr_ttc><frais_vo>0</frais_vo><frais_vo_detail>NA</frais_vo_detail>
<vn_vo>VN</vn_vo><remise_france>0</remise_france><options_catalogue_fr></options_catalogue_fr>
<modele_catalogue_fr><![CDATA[TEST]]></modele_catalogue_fr><destockage>NON</destockage>
<prix_argus></prix_argus><ancien_prix></ancien_prix><user_type>BE</user_type><new></new>
<remarque_catalogue><![CDATA[]]></remarque_catalogue><date_crea>2024-01-01</date_crea>
<prix_part_tvac>0</prix_part_tvac><codes_pub>SSL//02</codes_pub><tags><![CDATA[TEST]]></tags>
<exclude></exclude><code_boite>2</code_boite><code_couleur>1</code_couleur>
<equipments_principaux><equipment>1</equipment></equipments_principaux>
<carlab>0</carlab><carpass_url></carpass_url><malus>0</malus><etat></etat>
<auction><datetime_end>-0-0 0:0:0</datetime_end><min_price></min_price><prix_reserve></prix_reserve><description></description></auction>
<stock>1</stock><okcars>0</okcars><ecarlux>0</ecarlux><publish><platform>test</platform></publish>
</item>
</data>
XML;

$venduXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data><time>test</time><total_items>1</total_items>
<item>
<id>{$xmlCarId}</id>
<id_produit>O{$xmlCarId}</id_produit>
<id_client>C9999</id_client>
<date_commande>2026-05-01</date_commande>
<statut>LIVRE</statut>
</item>
</data>
XML;

echo "=== Step 1: Import stock car (id={$xmlCarId}) ===\n";
app(CarXmlImportService::class)->import([
    'content' => $carsXml,
    'filename' => 'lifecycle-test.xml',
    'user_id' => null,
]);

$car = Car::query()->findOrFail($xmlCarId);
echo "cars: id={$car->id} ref={$car->id_produit} stock_status={$car->stock_status}\n";
printHistory($xmlCarId);

echo "\n=== Step 2: Import vendu (sold) ===\n";
$stats = app(VenduXmlImportService::class)->import($venduXml);
echo 'vendu stats: '.json_encode($stats)."\n";

$car->refresh();
echo "cars: id={$car->id} ref={$car->id_produit} stock_status={$car->stock_status}\n";
printHistory($xmlCarId);

echo "\nDone. Expected: IMPORTED then SOLD with buyer_info.\n";

function printHistory(int $carId): void
{
    $rows = CarHistory::query()->where('car_id', $carId)->orderBy('id')->get();
    if ($rows->isEmpty()) {
        echo "car_history: (empty)\n";

        return;
    }
    foreach ($rows as $row) {
        echo "  - {$row->status} @ {$row->created_at}";
        if ($row->buyer_info) {
            echo ' | buyer: '.json_encode($row->buyer_info);
        }
        echo "\n";
    }
}
