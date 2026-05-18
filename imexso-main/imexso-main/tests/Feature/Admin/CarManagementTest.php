<?php

namespace Tests\Feature\Admin;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CarManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_cannot_access_car_list(): void
    {
        $this->get(route('admin.cars.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_car_list(): void
    {
        $user = User::factory()->validated()->create();

        $this->actingAs($user)
            ->get(route('admin.cars.index'))
            ->assertForbidden();
    }

    public function test_admin_can_list_cars(): void
    {
        Car::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/cars/index')
                ->has('cars.data', 3)
                ->has('makes')
            );
    }

    public function test_admin_can_filter_cars_by_sync_status(): void
    {
        Car::factory()->count(2)->create(['sync_status' => 'active']);
        Car::factory()->count(3)->create(['sync_status' => 'sold']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index', ['sync_status' => 'active']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('cars.data', 2)
            );
    }

    public function test_admin_can_filter_cars_by_make(): void
    {
        Car::factory()->create(['make' => 'BMW']);
        Car::factory()->create(['make' => 'AUDI']);
        Car::factory()->create(['make' => 'BMW']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index', ['make' => 'BMW']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('cars.data', 2)
            );
    }

    public function test_admin_can_search_cars(): void
    {
        Car::factory()->create(['make' => 'BMW', 'model' => 'X5']);
        Car::factory()->create(['make' => 'AUDI', 'model' => 'A4']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.index', ['search' => 'BMW']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('cars.data', 1)
            );
    }

    public function test_admin_can_access_import_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.cars.import'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/cars/import')
            );
    }

    public function test_guests_cannot_access_import_page(): void
    {
        $this->get(route('admin.cars.import'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_import_xml(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(2));

        $response = $this->actingAs($this->admin)
            ->post(route('admin.cars.import.store'), ['file' => $file]);

        $response->assertRedirect(route('admin.cars.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('cars', 2);
        $this->assertDatabaseCount('car_imports', 1);
        $this->assertDatabaseHas('car_imports', ['status' => 'completed']);
    }

    public function test_import_requires_xml_file(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.cars.import.store'), []);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_non_xml_file(): void
    {
        $file = UploadedFile::fake()->create('data.csv', 100, 'text/csv');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.cars.import.store'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_handles_invalid_xml(): void
    {
        $file = UploadedFile::fake()->createWithContent('cars.xml', '<broken');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.cars.import.store'), ['file' => $file]);

        $response->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_non_admin_cannot_import(): void
    {
        $user = User::factory()->validated()->create();
        $file = UploadedFile::fake()->createWithContent('cars.xml', $this->buildValidXml(1));

        $this->actingAs($user)
            ->post(route('admin.cars.import.store'), ['file' => $file])
            ->assertForbidden();
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
<option><nom><![CDATA[peinture]]></nom><prix>642</prix></option>
</options_catalogue_fr>
<modele_catalogue_fr><![CDATA[CITROEN C5 X]]></modele_catalogue_fr>
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
<publish><platform>aramis</platform></publish>
</item>
XML;
    }
}
