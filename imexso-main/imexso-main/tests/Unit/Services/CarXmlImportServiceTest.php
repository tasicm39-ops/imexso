<?php

namespace Tests\Unit\Services;

use App\Services\CarHistoryService;
use App\Services\CarXmlImportService;
use PHPUnit\Framework\TestCase;

class CarXmlImportServiceTest extends TestCase
{
    private CarXmlImportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CarXmlImportService(new CarHistoryService);
    }

    public function test_parse_xml_with_valid_xml(): void
    {
        $xml = $this->buildMinimalXml();

        $result = $this->service->parseXml($xml);

        $this->assertNotNull($result);
        $this->assertEquals('1', (string) $result->total_items);
    }

    public function test_parse_xml_with_invalid_xml_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid XML');

        $this->service->parseXml('<invalid><broken>');
    }

    public function test_parse_xml_with_empty_string_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->parseXml('');
    }

    public function test_extract_items_returns_items(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXml());
        $items = $this->service->extractItems($xml);

        $this->assertCount(1, $items);
        $this->assertEquals('TEST001', (string) $items[0]->id_produit);
    }

    public function test_extract_items_with_no_items_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not contain any <item>');

        $xml = $this->service->parseXml('<data><time>test</time><total_items>0</total_items></data>');
        $this->service->extractItems($xml);
    }

    public function test_parse_status_splits_by_double_percent(): void
    {
        $result = $this->service->parseStatus('En stock%%Auf Lager%%On Stock%%Stock');

        $this->assertEquals([
            'fr' => 'En stock',
            'de' => 'Auf Lager',
            'en' => 'On Stock',
            'nl' => 'Stock',
        ], $result);
    }

    public function test_parse_status_handles_fewer_parts(): void
    {
        $result = $this->service->parseStatus('En stock%%Auf Lager');

        $this->assertEquals([
            'fr' => 'En stock',
            'de' => 'Auf Lager',
            'en' => '',
            'nl' => '',
        ], $result);
    }

    public function test_parse_status_handles_single_value(): void
    {
        $result = $this->service->parseStatus('Available');

        $this->assertEquals([
            'fr' => 'Available',
            'de' => '',
            'en' => '',
            'nl' => '',
        ], $result);
    }

    public function test_parse_equipment_extracts_multilingual_items(): void
    {
        $xml = simplexml_load_string('
            <item>
                <equipments_supplementaires>
                    <equipments_supplementaires_fr>
                        <equipment><![CDATA[cuir noir]]></equipment>
                        <equipment><![CDATA[peinture métallisée]]></equipment>
                    </equipments_supplementaires_fr>
                    <equipments_supplementaires_en>
                        <equipment><![CDATA[black leather]]></equipment>
                        <equipment><![CDATA[metallic paint]]></equipment>
                    </equipments_supplementaires_en>
                </equipments_supplementaires>
            </item>
        ');

        $result = $this->service->parseEquipment(
            $xml,
            'equipments_supplementaires',
            'equipments_supplementaires_',
        );

        $this->assertArrayHasKey('fr', $result);
        $this->assertArrayHasKey('en', $result);
        $this->assertEquals(['cuir noir', 'peinture métallisée'], $result['fr']);
        $this->assertEquals(['black leather', 'metallic paint'], $result['en']);
    }

    public function test_parse_equipment_skips_empty_items(): void
    {
        $xml = simplexml_load_string('
            <item>
                <equipments_supplementaires>
                    <equipments_supplementaires_pl>
                        <equipment><![CDATA[]]></equipment>
                    </equipments_supplementaires_pl>
                </equipments_supplementaires>
            </item>
        ');

        $result = $this->service->parseEquipment(
            $xml,
            'equipments_supplementaires',
            'equipments_supplementaires_',
        );

        $this->assertArrayNotHasKey('pl', $result);
    }

    public function test_parse_equipment_handles_duplicate_en_entries(): void
    {
        $xml = simplexml_load_string('
            <item>
                <equipments_supplementaires>
                    <equipments_supplementaires_en>
                        <equipment><![CDATA[first set]]></equipment>
                    </equipments_supplementaires_en>
                    <equipments_supplementaires_en>
                        <equipment><![CDATA[duplicate set]]></equipment>
                    </equipments_supplementaires_en>
                </equipments_supplementaires>
            </item>
        ');

        $result = $this->service->parseEquipment(
            $xml,
            'equipments_supplementaires',
            'equipments_supplementaires_',
        );

        $this->assertCount(1, $result['en']);
        $this->assertEquals(['first set'], $result['en']);
    }

    public function test_map_xml_item_maps_all_scalar_fields(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXml());
        $items = $this->service->extractItems($xml);

        $attributes = $this->service->mapXmlItemToCarAttributes($items[0]);

        $this->assertEquals('TEST001', $attributes['id_produit']);
        $this->assertEquals('TESTVIN12345678901', $attributes['vin']);
        $this->assertEquals('CITROEN', $attributes['make']);
        $this->assertEquals('C5 X', $attributes['model']);
        $this->assertEquals('PLUS', $attributes['trim_level']);
        $this->assertEquals('ESSENCE', $attributes['fuel_type']);
        $this->assertEquals(1200, $attributes['engine_displacement']);
        $this->assertEquals(130, $attributes['horsepower']);
        $this->assertEquals('ESS', $attributes['engine_code']);
        $this->assertEquals(1493, $attributes['weight']);
        $this->assertEquals(2024, $attributes['manufacturing_year']);
        $this->assertEquals('EAT8', $attributes['gearbox']);
        $this->assertEquals('2', $attributes['gearbox_code']);
        $this->assertEquals('BLEU', $attributes['color']);
        $this->assertEquals('4', $attributes['color_code']);
        $this->assertEquals(10, $attributes['mileage']);
        $this->assertEquals(137, $attributes['co2_wltp']);
        $this->assertEquals('EURO6', $attributes['euro_standard']);
        $this->assertEquals(20100.0, $attributes['professional_price']);
        $this->assertEquals('HTVA', $attributes['vat_type']);
        $this->assertEquals('VN', $attributes['condition_type']);
        $this->assertEquals('BE', $attributes['user_type']);
        $this->assertEquals('2024-06-27', $attributes['creation_date']);
        $this->assertEquals(5, $attributes['doors']);
        $this->assertTrue($attributes['is_clearance']);
        $this->assertFalse($attributes['is_new']);
        $this->assertEquals('active', $attributes['sync_status']);
        $this->assertEquals('AVAILABLE', $attributes['stock_status']);
    }

    public function test_resolve_manufacturing_year_from_registration_date_when_fabrication_empty(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXmlWithEmptyFields(
            dateImmat: '2024-06-29',
        ));
        $items = $this->service->extractItems($xml);

        $this->assertSame(2024, $this->service->resolveManufacturingYear($items[0]));
        $this->assertSame(2024, $this->service->mapXmlItemToCarAttributes($items[0])['manufacturing_year']);
    }

    public function test_map_xml_item_maps_retention_date_from_xml(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXmlWithEmptyFields(
            dateImmat: '2024-06-29',
            dateCrea: '',
            retention: '2026-06-01',
        ));
        $items = $this->service->extractItems($xml);

        $attributes = $this->service->mapXmlItemToCarAttributes($items[0]);

        $this->assertSame('2026-06-01', $attributes['retention_date']);
    }

    public function test_map_xml_item_handles_nullable_fields(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXmlWithEmptyFields());
        $items = $this->service->extractItems($xml);

        $attributes = $this->service->mapXmlItemToCarAttributes($items[0]);

        $this->assertNull($attributes['vin']);
        $this->assertNull($attributes['weight']);
        $this->assertNull($attributes['manufacturing_year']);
        $this->assertNull($attributes['co2']);
        $this->assertNull($attributes['wltp_electric_range']);
        $this->assertNull($attributes['registration_date']);
        $this->assertNull($attributes['retention_date']);
        $this->assertNull($attributes['warranty_start_date']);
        $this->assertNull($attributes['doors']);
        $this->assertNull($attributes['category']);
        $this->assertNull($attributes['argus_price']);
    }

    public function test_map_xml_item_parses_boolean_fields(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXml());
        $items = $this->service->extractItems($xml);

        $attributes = $this->service->mapXmlItemToCarAttributes($items[0]);

        $this->assertTrue($attributes['is_clearance']);
        $this->assertFalse($attributes['is_new']);
        $this->assertTrue($attributes['carlab']);
        $this->assertFalse($attributes['okcars']);
        $this->assertFalse($attributes['ecarlux']);
    }

    public function test_map_xml_item_parses_status_correctly(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXml());
        $items = $this->service->extractItems($xml);

        $attributes = $this->service->mapXmlItemToCarAttributes($items[0]);

        $this->assertEquals([
            'fr' => 'En stock',
            'de' => 'Auf Lager',
            'en' => 'On Stock',
            'nl' => 'Stock',
        ], $attributes['status']);
    }

    public function test_map_xml_item_parses_publish_platforms(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXml());
        $items = $this->service->extractItems($xml);

        $attributes = $this->service->mapXmlItemToCarAttributes($items[0]);

        $this->assertEquals(['aramis', 'carconnex'], $attributes['publish_platforms']);
    }

    public function test_map_xml_item_parses_main_equipment_codes(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXml());
        $items = $this->service->extractItems($xml);

        $attributes = $this->service->mapXmlItemToCarAttributes($items[0]);

        $this->assertEquals([7], $attributes['main_equipment_codes']);
    }

    public function test_parse_excluded_countries_with_multiple_countries(): void
    {
        $xml = simplexml_load_string('
            <item>
                <exclude>
                    <pays>Belgium</pays>
                    <pays>Luxembourg</pays>
                </exclude>
            </item>
        ');

        $result = $this->service->parseExcludedCountries($xml);

        $this->assertEquals(['Belgium', 'Luxembourg'], $result);
    }

    public function test_parse_excluded_countries_with_empty_exclude(): void
    {
        $xml = simplexml_load_string('
            <item>
                <exclude></exclude>
            </item>
        ');

        $result = $this->service->parseExcludedCountries($xml);

        $this->assertEmpty($result);
    }

    public function test_parse_excluded_countries_with_single_country(): void
    {
        $xml = simplexml_load_string('
            <item>
                <exclude>
                    <pays>Italy</pays>
                </exclude>
            </item>
        ');

        $result = $this->service->parseExcludedCountries($xml);

        $this->assertEquals(['Italy'], $result);
    }

    public function test_parse_excluded_countries_without_exclude_element(): void
    {
        $xml = simplexml_load_string('<item></item>');

        $result = $this->service->parseExcludedCountries($xml);

        $this->assertEmpty($result);
    }

    public function test_map_xml_item_includes_excluded_countries(): void
    {
        $xml = $this->service->parseXml($this->buildMinimalXml());
        $items = $this->service->extractItems($xml);

        $attributes = $this->service->mapXmlItemToCarAttributes($items[0]);

        $this->assertEquals(['Belgium', 'Luxembourg'], $attributes['excluded_countries']);
    }

    private function buildMinimalXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>
<time>13/03/2026 14:12:51</time>
<total_items>1</total_items>
<item>
<count>1</count>
<id>196318</id>
<id_produit>TEST001</id_produit>
<vin>TESTVIN12345678901</vin>
<marque><![CDATA[CITROEN]]></marque>
<modele><![CDATA[C5 X]]></modele>
<finition><![CDATA[PLUS]]></finition>
<carburant><![CDATA[ESSENCE]]></carburant>
<cylindre>1200</cylindre>
<puissance>130</puissance>
<moteur><![CDATA[ESS]]></moteur>
<poids>1493</poids>
<equipments_supplementaires>
<equipments_supplementaires_fr>
<equipment><![CDATA[cuir noir]]></equipment>
</equipments_supplementaires_fr>
<equipments_supplementaires_en>
<equipment><![CDATA[black leather]]></equipment>
</equipments_supplementaires_en>
</equipments_supplementaires>
<equipments_serie>
<equipments_serie_fr>
<equipment><![CDATA[6 airbags]]></equipment>
</equipments_serie_fr>
<equipments_serie_en>
<equipment><![CDATA[6 airbags]]></equipment>
</equipments_serie_en>
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
<photo>https://photos.imexso.com/photos_vehicules/TEST001-1.jpg</photo>
<photo>https://photos.imexso.com/photos_vehicules/TEST001-2.jpg</photo>
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
<option>
<nom><![CDATA[peinture métallisée]]></nom><prix>642</prix>
</option>
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
<tags><![CDATA[TEST001 %% CITROEN %% C5 X]]></tags>
<exclude>
<pays>Belgium</pays>
<pays>Luxembourg</pays>
</exclude>
<code_boite>2</code_boite>
<code_couleur>4</code_couleur>
<equipments_principaux>
<equipment>7</equipment>
</equipments_principaux>
<carlab>1</carlab>
<carpass_url></carpass_url>
<malus>0</malus>
<etat></etat>
<auction>
<datetime_end>-0-0 0:0:0</datetime_end>
<min_price></min_price>
<prix_reserve></prix_reserve>
<description><![CDATA[]]></description>
</auction>
<stock>2</stock>
<okcars>0</okcars>
<ecarlux>0</ecarlux>
<publish>
<platform>aramis</platform>
<platform>carconnex</platform>
</publish>
</item>
</data>
XML;
    }

    private function buildMinimalXmlWithEmptyFields(
        ?string $dateImmat = null,
        ?string $dateCrea = null,
        ?string $retention = null,
    ): string {
        $dateImmat = $dateImmat ?? '';
        $dateCrea = $dateCrea ?? '';
        $retention = $retention ?? '';

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data>
<time>13/03/2026 14:12:51</time>
<total_items>1</total_items>
<item>
<count>1</count>
<id>196319</id>
<id_produit>EMPTY001</id_produit>
<vin></vin>
<marque><![CDATA[PEUGEOT]]></marque>
<modele><![CDATA[308]]></modele>
<finition><![CDATA[ACTIVE]]></finition>
<carburant><![CDATA[DIESEL]]></carburant>
<cylindre>1500</cylindre>
<puissance>130</puissance>
<moteur><![CDATA[HDI]]></moteur>
<poids></poids>
<equipments_supplementaires>
<equipments_supplementaires_fr><equipment><![CDATA[]]></equipment></equipments_supplementaires_fr>
</equipments_supplementaires>
<equipments_serie>
<equipments_serie_fr><equipment><![CDATA[abs]]></equipment></equipments_serie_fr>
</equipments_serie>
<annee_fabrication></annee_fabrication>
<boite><![CDATA[BVA]]></boite>
<couleur><![CDATA[BLANC]]></couleur>
<km>50000</km>
<co2></co2>
<co2wltp></co2wltp>
<wltp_erange></wltp_erange>
<norme_euro></norme_euro>
<prix_pro>15000</prix_pro>
<tva>TTC</tva>
<statut><![CDATA[En stock%%Auf Lager%%On Stock%%Stock]]></statut>
<date_immat>{$dateImmat}</date_immat>
<retention>{$retention}</retention>
<date_depart_garantie></date_depart_garantie>
<portes></portes>
<photos>
<photo>https://photos.imexso.com/photos_vehicules/EMPTY001-1.jpg</photo>
</photos>
<categorie></categorie>
<prix_base_cat_fr_htva></prix_base_cat_fr_htva>
<prix_tot_cat_fr_htva></prix_tot_cat_fr_htva>
<prix_catalogue_fr_ttc></prix_catalogue_fr_ttc>
<frais_vo>0</frais_vo>
<frais_vo_detail>NA</frais_vo_detail>
<vn_vo>VO</vn_vo>
<remise_france>0</remise_france>
<options_catalogue_fr></options_catalogue_fr>
<modele_catalogue_fr></modele_catalogue_fr>
<destockage></destockage>
<prix_argus></prix_argus>
<ancien_prix></ancien_prix>
<user_type>INT</user_type>
<new></new>
<remarque_catalogue></remarque_catalogue>
<date_crea>{$dateCrea}</date_crea>
<prix_part_tvac>0</prix_part_tvac>
<codes_pub>N/A//N/A</codes_pub>
<tags><![CDATA[EMPTY001 %% PEUGEOT %% 308]]></tags>
<exclude></exclude>
<code_boite>1</code_boite>
<code_couleur></code_couleur>
<equipments_principaux><equipment>9</equipment></equipments_principaux>
<carlab></carlab>
<carpass_url></carpass_url>
<malus></malus>
<etat></etat>
<auction><datetime_end>-0-0 0:0:0</datetime_end><min_price></min_price><prix_reserve></prix_reserve><description><![CDATA[]]></description></auction>
<stock></stock>
<okcars>0</okcars>
<ecarlux>0</ecarlux>
<publish></publish>
</item>
</data>
XML;
    }
}
