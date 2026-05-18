<?php

namespace Tests\Feature\Console;

use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportAllCarsXmlCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_all_runs_stock_then_vendu(): void
    {
        $stockXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
<total_items>1</total_items>
<item>
<count>1</count>
<id>777001</id>
<id_produit>N777001</id_produit>
<vin>VIN777001TEST00001</vin>
<marque><![CDATA[TEST]]></marque>
<modele><![CDATA[STOCK]]></modele>
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
<photos><photo>https://example.com/777001-1.jpg</photo></photos>
<categorie><![CDATA[Berline]]></categorie>
<prix_base_cat_fr_htva>10000</prix_base_cat_fr_htva>
<prix_tot_cat_fr_htva>12000</prix_tot_cat_fr_htva>
<prix_catalogue_fr_ttc>14000</prix_catalogue_fr_ttc>
<frais_vo>0</frais_vo>
<frais_vo_detail>NA</frais_vo_detail>
<vn_vo>VN</vn_vo>
<remise_france>0</remise_france>
<options_catalogue_fr></options_catalogue_fr>
<modele_catalogue_fr><![CDATA[TEST STOCK]]></modele_catalogue_fr>
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

        $venduXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
<total_items>2</total_items>
<item>
<id>777001</id>
<id_produit>N777001</id_produit>
<id_client>C777</id_client>
</item>
<item>
<id>777002</id>
<id_produit>N777002</id_produit>
<id_client>C778</id_client>
</item>
</data>
XML;

        $stockPath = storage_path('framework/testing-stock.xml');
        $venduPath = storage_path('framework/testing-vendu.xml');
        file_put_contents($stockPath, $stockXml);
        file_put_contents($venduPath, $venduXml);

        $exitCode = Artisan::call('cars:import-all', [
            '--file-stock' => $stockPath,
            '--file-vendu' => $venduPath,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('cars', ['id' => 777001, 'sync_status' => 'sold']);
        $this->assertTrue(Car::query()->where('id', 777002)->exists());
        $this->assertDatabaseHas('car_history', ['car_id' => 777002, 'status' => 'SOLD']);

        @unlink($stockPath);
        @unlink($venduPath);
    }
}
