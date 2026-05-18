<?php

namespace Tests\Feature\Api;

use App\Models\Car;
use App\Models\CarHistory;
use App\Models\CarImport;
use App\Models\CarOption;
use App\Models\CarPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CarImportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->admin()->create();
    }

    public function test_guests_cannot_import_cars(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $this->postJson(route('api.cars.import'), ['file' => $file])
            ->assertUnauthorized();
    }

    public function test_non_admin_users_cannot_import_cars(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $this->actingAs(User::factory()->validated()->create())
            ->postJson(route('api.cars.import'), ['file' => $file])
            ->assertForbidden();
    }

    public function test_import_requires_a_file(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_import_rejects_non_xml_file(): void
    {
        $file = UploadedFile::fake()->create('data.csv', 100, 'text/csv');

        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_import_creates_new_cars(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(2));

        $response = $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.total_items_in_xml', 2)
            ->assertJsonPath('data.new_count', 2)
            ->assertJsonPath('data.updated_count', 0)
            ->assertJsonPath('data.sold_count', 0);

        $this->assertDatabaseCount('cars', 2);
        $this->assertDatabaseHas('cars', ['id' => 100001, 'id_produit' => 'IMPORT001']);
        $this->assertDatabaseHas('cars', ['id' => 100002, 'id_produit' => 'IMPORT002']);
        $this->assertDatabaseCount('car_history', 2);
        $this->assertDatabaseHas('car_history', ['car_id' => 100001, 'status' => 'IMPORTED']);
    }

    public function test_import_updates_existing_cars(): void
    {
        Car::factory()->create([
            'id' => 100001,
            'id_produit' => 'IMPORT001',
            'make' => 'OLD_MAKE',
            'professional_price' => 10000,
            'sync_status' => 'active',
        ]);

        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $response = $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file]);

        $response->assertOk()
            ->assertJsonPath('data.new_count', 0)
            ->assertJsonPath('data.updated_count', 1);

        $this->assertDatabaseHas('cars', [
            'id_produit' => 'IMPORT001',
            'make' => 'CITROEN',
        ]);
    }

    public function test_import_does_not_mark_missing_cars_as_sold(): void
    {
        Car::factory()->create([
            'id' => 200001,
            'id_produit' => 'EXISTING001',
            'sync_status' => 'active',
            'stock_status' => 'AVAILABLE',
        ]);
        Car::factory()->create([
            'id' => 200002,
            'id_produit' => 'EXISTING002',
            'sync_status' => 'active',
            'stock_status' => 'AVAILABLE',
        ]);

        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $response = $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file]);

        $response->assertOk()
            ->assertJsonPath('data.sold_count', 0);

        $this->assertDatabaseHas('cars', [
            'id_produit' => 'EXISTING001',
            'sync_status' => 'active',
            'stock_status' => 'AVAILABLE',
        ]);
        $this->assertDatabaseHas('cars', [
            'id_produit' => 'EXISTING002',
            'sync_status' => 'active',
            'stock_status' => 'AVAILABLE',
        ]);
    }

    public function test_import_syncs_photos(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file])
            ->assertOk();

        $car = Car::query()->where('id_produit', 'IMPORT001')->first();
        $this->assertNotNull($car);

        $photos = CarPhoto::query()->where('car_id', $car->id)->orderBy('position')->get();
        $this->assertCount(2, $photos);
        $this->assertEquals('https://photos.imexso.com/photos_vehicules/IMPORT001-1.jpg', $photos[0]->url);
        $this->assertEquals(0, $photos[0]->position);
        $this->assertEquals(1, $photos[1]->position);
    }

    public function test_import_syncs_options(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file])
            ->assertOk();

        $car = Car::query()->where('id_produit', 'IMPORT001')->first();
        $options = CarOption::query()->where('car_id', $car->id)->get();
        $this->assertCount(1, $options);
        $this->assertEquals('peinture métallisée', $options[0]->name);
        $this->assertEquals('642.00', $options[0]->price);
    }

    public function test_import_replaces_photos_on_update(): void
    {
        $car = Car::factory()->create(['id' => 100001, 'id_produit' => 'IMPORT001', 'sync_status' => 'active']);
        CarPhoto::factory()->count(5)->create(['car_id' => $car->id]);

        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file])
            ->assertOk();

        $this->assertEquals(2, CarPhoto::query()->where('car_id', $car->id)->count());
    }

    public function test_import_creates_import_record(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file])
            ->assertOk();

        $this->assertDatabaseCount('car_imports', 1);

        $import = CarImport::query()->first();
        $this->assertEquals('completed', $import->status);
        $this->assertEquals($this->user->id, $import->user_id);
        $this->assertEquals('cars.xml', $import->filename);
        $this->assertNotNull($import->started_at);
        $this->assertNotNull($import->completed_at);
    }

    public function test_import_rejects_invalid_xml_content(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', '<invalid><broken>');

        $response = $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', fn (string $msg) => str_contains($msg, 'Invalid XML'));

        $this->assertDatabaseHas('car_imports', ['status' => 'failed']);
    }

    public function test_import_rejects_xml_with_missing_required_fields(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
<time>test</time>
<total_items>1</total_items>
<item>
<count>1</count>
<id_produit>BAD001</id_produit>
<vin></vin>
<marque></marque>
<modele></modele>
<finition></finition>
<carburant></carburant>
<cylindre>abc</cylindre>
<puissance>130</puissance>
<moteur></moteur>
<poids></poids>
<equipments_supplementaires></equipments_supplementaires>
<equipments_serie></equipments_serie>
<annee_fabrication></annee_fabrication>
<boite></boite>
<couleur></couleur>
<km>10</km>
<co2></co2>
<co2wltp></co2wltp>
<wltp_erange></wltp_erange>
<norme_euro></norme_euro>
<prix_pro>20100</prix_pro>
<tva>HTVA</tva>
<statut>test</statut>
<date_immat></date_immat>
<retention></retention>
<date_depart_garantie></date_depart_garantie>
<portes></portes>
<photos></photos>
<categorie></categorie>
<prix_base_cat_fr_htva></prix_base_cat_fr_htva>
<prix_tot_cat_fr_htva></prix_tot_cat_fr_htva>
<prix_catalogue_fr_ttc></prix_catalogue_fr_ttc>
<frais_vo>0</frais_vo>
<frais_vo_detail></frais_vo_detail>
<vn_vo>VN</vn_vo>
<remise_france>0</remise_france>
<options_catalogue_fr></options_catalogue_fr>
<modele_catalogue_fr></modele_catalogue_fr>
<destockage></destockage>
<prix_argus></prix_argus>
<ancien_prix></ancien_prix>
<user_type>BE</user_type>
<new></new>
<remarque_catalogue></remarque_catalogue>
<date_crea>2024-06-27</date_crea>
<prix_part_tvac>0</prix_part_tvac>
<codes_pub>SSL//02</codes_pub>
<tags>test</tags>
<exclude></exclude>
<code_boite>2</code_boite>
<code_couleur></code_couleur>
<equipments_principaux><equipment>7</equipment></equipments_principaux>
<carlab>0</carlab>
<carpass_url></carpass_url>
<malus></malus>
<etat></etat>
<auction><datetime_end></datetime_end><min_price></min_price><prix_reserve></prix_reserve><description></description></auction>
<stock></stock>
<okcars>0</okcars>
<ecarlux>0</ecarlux>
<publish></publish>
</item>
</data>
XML;

        $file = UploadedFile::fake()->createWithContent('cars.xml', $xml);

        $response = $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file]);

        $response->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['BAD001']]);
    }

    public function test_double_import_is_idempotent(): void
    {
        $xmlContent = $this->buildValidXml(2);

        $file1 = UploadedFile::fake()->createWithContent('cars.xml', $xmlContent);
        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file1])
            ->assertOk();

        $this->assertDatabaseCount('cars', 2);

        $file2 = UploadedFile::fake()->createWithContent('cars.xml', $xmlContent);
        $response = $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file2]);

        $response->assertOk()
            ->assertJsonPath('data.new_count', 0)
            ->assertJsonPath('data.sold_count', 0);

        $this->assertDatabaseCount('cars', 2);
        $this->assertDatabaseCount('car_imports', 2);
    }

    public function test_import_stores_excluded_countries(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file])
            ->assertOk();

        $car = Car::query()->where('id_produit', 'IMPORT001')->first();
        $this->assertNotNull($car);
        $this->assertEquals(['Belgium', 'Luxembourg'], $car->excluded_countries);
    }

    public function test_import_reactivates_previously_sold_car_when_present_in_feed(): void
    {
        Car::factory()->create([
            'id' => 100001,
            'id_produit' => 'IMPORT001',
            'sync_status' => 'sold',
            'stock_status' => 'SOLD',
            'make' => 'OLD',
        ]);

        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $response = $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file]);

        $response->assertOk();

        $this->assertDatabaseHas('cars', [
            'id' => 100001,
            'id_produit' => 'IMPORT001',
            'sync_status' => 'active',
            'stock_status' => 'AVAILABLE',
            'make' => 'CITROEN',
        ]);
        $this->assertDatabaseHas('car_history', [
            'car_id' => 100001,
            'status' => 'AVAILABLE',
        ]);
    }

    public function test_import_updates_id_produit_when_reference_changes(): void
    {
        Car::factory()->create([
            'id' => 100001,
            'id_produit' => 'OLDREF001',
            'sync_status' => 'active',
        ]);

        $xml = str_replace('<id_produit>IMPORT001</id_produit>', '<id_produit>N196318</id_produit>', $this->buildValidXml(1));
        $file = UploadedFile::fake()->createWithContent('cars.xml', $xml);

        $this->actingAs($this->user)
            ->postJson(route('api.cars.import'), ['file' => $file])
            ->assertOk();

        $this->assertDatabaseHas('cars', [
            'id' => 100001,
            'id_produit' => 'N196318',
        ]);
    }

    private function buildValidXml(int $itemCount): string
    {
        $items = '';
        for ($i = 1; $i <= $itemCount; $i++) {
            $id = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $items .= $this->buildXmlItem("IMPORT{$id}", $i, 100000 + $i);
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data>
<time>13/03/2026 14:12:51</time>
<total_items>{$itemCount}</total_items>
{$items}
</data>
XML;
    }

    private function buildXmlItem(string $idProduit, int $count, int $xmlId): string
    {
        return <<<XML
<item>
<count>{$count}</count>
<id>{$xmlId}</id>
<id_produit>{$idProduit}</id_produit>
<vin>VIN{$idProduit}12345678</vin>
<marque><![CDATA[CITROEN]]></marque>
<modele><![CDATA[C5 X]]></modele>
<finition><![CDATA[PLUS]]></finition>
<carburant><![CDATA[ESSENCE]]></carburant>
<cylindre>1200</cylindre>
<puissance>130</puissance>
<moteur><![CDATA[ESS]]></moteur>
<poids>1493</poids>
<equipments_supplementaires>
<equipments_supplementaires_fr><equipment><![CDATA[cuir noir]]></equipment></equipments_supplementaires_fr>
<equipments_supplementaires_en><equipment><![CDATA[black leather]]></equipment></equipments_supplementaires_en>
</equipments_supplementaires>
<equipments_serie>
<equipments_serie_fr><equipment><![CDATA[6 airbags]]></equipment></equipments_serie_fr>
<equipments_serie_en><equipment><![CDATA[6 airbags]]></equipment></equipments_serie_en>
</equipments_serie>
<annee_fabrication>2024</annee_fabrication>
<boite><![CDATA[EAT8]]></boite>
<couleur><![CDATA[BLEU]]></couleur>
<km>10</km>
<co2></co2>
<co2wltp>137</co2wltp>
<wltp_erange></wltp_erange>
<norme_euro>EURO6</norme_euro>
<prix_pro>20100</prix_pro>
<tva>HTVA</tva>
<statut><![CDATA[En stock%%Auf Lager%%On Stock%%Stock]]></statut>
<date_immat>2024-06-29</date_immat>
<retention></retention>
<date_depart_garantie></date_depart_garantie>
<portes>5</portes>
<photos>
<photo>https://photos.imexso.com/photos_vehicules/{$idProduit}-1.jpg</photo>
<photo>https://photos.imexso.com/photos_vehicules/{$idProduit}-2.jpg</photo>
</photos>
<categorie><![CDATA[Berline]]></categorie>
<prix_base_cat_fr_htva>34791.67</prix_base_cat_fr_htva>
<prix_tot_cat_fr_htva>36800</prix_tot_cat_fr_htva>
<prix_catalogue_fr_ttc>44160</prix_catalogue_fr_ttc>
<frais_vo>0</frais_vo>
<frais_vo_detail>NA</frais_vo_detail>
<vn_vo>VN</vn_vo>
<remise_france>45</remise_france>
<options_catalogue_fr>
<option><nom><![CDATA[peinture métallisée]]></nom><prix>642</prix></option>
</options_catalogue_fr>
<modele_catalogue_fr><![CDATA[CITROEN C5 X PureTech 130]]></modele_catalogue_fr>
<destockage>OUI</destockage>
<prix_argus></prix_argus>
<ancien_prix></ancien_prix>
<user_type>BE</user_type>
<new></new>
<remarque_catalogue><![CDATA[]]></remarque_catalogue>
<date_crea>2024-06-27</date_crea>
<prix_part_tvac>28000</prix_part_tvac>
<codes_pub>SSL//02</codes_pub>
<tags><![CDATA[{$idProduit} %% CITROEN %% C5 X]]></tags>
<exclude>
<pays>Belgium</pays>
<pays>Luxembourg</pays>
</exclude>
<code_boite>2</code_boite>
<code_couleur>4</code_couleur>
<equipments_principaux><equipment>7</equipment></equipments_principaux>
<carlab>1</carlab>
<carpass_url></carpass_url>
<malus>0</malus>
<etat></etat>
<auction><datetime_end>-0-0 0:0:0</datetime_end><min_price></min_price><prix_reserve></prix_reserve><description><![CDATA[]]></description></auction>
<stock>2</stock>
<okcars>0</okcars>
<ecarlux>0</ecarlux>
<publish><platform>aramis</platform><platform>carconnex</platform></publish>
</item>
XML;
    }
}
