<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarImport;
use App\Models\CarOption;
use App\Models\CarPhoto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;

class CarXmlImportService
{
    /**
     * @var list<string>
     */
    private const EQUIPMENT_LANGUAGES = ['fr', 'en', 'de', 'nl', 'it', 'pl', 'es'];

    /**
     * @var array<string, string>
     */
    private const STATUS_LANGUAGE_MAP = [
        0 => 'fr',
        1 => 'de',
        2 => 'en',
        3 => 'nl',
    ];

    /**
     * @param  array{content: string, filename: string, user_id: int|null}  $params
     */
    public function import(array $params): CarImport
    {
        $import = CarImport::query()->create([
            'user_id' => $params['user_id'],
            'filename' => $params['filename'],
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $xml = $this->parseXml($params['content']);
            $items = $this->extractItems($xml);

            $import->update(['total_items_in_xml' => count($items)]);

            $this->validateItems($items);
            $this->syncCars($items, $import);

            $import->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }

        return $import->refresh();
    }

    public function parseXml(string $xmlContent): SimpleXMLElement
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($xmlContent, SimpleXMLElement::class, LIBXML_NOCDATA);

        if ($xml === false) {
            $errors = array_map(
                fn (\LibXMLError $error) => trim($error->message),
                libxml_get_errors(),
            );
            libxml_clear_errors();

            throw new \InvalidArgumentException(
                'Invalid XML: '.implode('; ', $errors),
            );
        }

        libxml_clear_errors();

        return $xml;
    }

    /**
     * @return list<SimpleXMLElement>
     */
    public function extractItems(SimpleXMLElement $xml): array
    {
        if (! isset($xml->item)) {
            throw new \InvalidArgumentException('XML does not contain any <item> elements.');
        }

        $items = [];
        foreach ($xml->item as $item) {
            $items[] = $item;
        }

        if (count($items) === 0) {
            throw new \InvalidArgumentException('XML contains zero items.');
        }

        return $items;
    }

    /**
     * @param  list<SimpleXMLElement>  $items
     *
     * @throws ValidationException
     */
    public function validateItems(array $items): void
    {
        $errors = [];
        $seenIds = [];

        foreach ($items as $index => $item) {
            $idProduit = $this->extractString($item, 'id_produit');
            $itemLabel = $idProduit ?: "item[{$index}]";

            if ($idProduit === null || $idProduit === '') {
                $errors[$itemLabel][] = 'id_produit is required.';

                continue;
            }

            if (isset($seenIds[$idProduit])) {
                $errors[$itemLabel][] = "Duplicate id_produit: {$idProduit}.";

                continue;
            }
            $seenIds[$idProduit] = true;

            $itemData = [
                'id_produit' => $idProduit,
                'make' => $this->extractString($item, 'marque'),
                'model' => $this->extractString($item, 'modele'),
                'trim_level' => $this->extractString($item, 'finition'),
                'fuel_type' => $this->extractString($item, 'carburant'),
                'engine_displacement' => $this->extractString($item, 'cylindre'),
                'horsepower' => $this->extractString($item, 'puissance'),
                'engine_code' => $this->extractString($item, 'moteur'),
                'gearbox' => $this->extractString($item, 'boite'),
                'color' => $this->extractString($item, 'couleur'),
                'mileage' => $this->extractString($item, 'km'),
                'professional_price' => $this->extractString($item, 'prix_pro'),
                'vat_type' => $this->extractString($item, 'tva'),
                'status' => $this->extractString($item, 'statut'),
                'condition_type' => $this->extractString($item, 'vn_vo'),
                'user_type' => $this->extractString($item, 'user_type'),
                'creation_date' => $this->extractString($item, 'date_crea'),
                'gearbox_code' => $this->extractString($item, 'code_boite'),
            ];

            $validator = Validator::make($itemData, [
                'id_produit' => ['required', 'string', 'max:50'],
                'make' => ['required', 'string', 'max:255'],
                'model' => ['required', 'string', 'max:255'],
                'trim_level' => ['required', 'string', 'max:255'],
                'fuel_type' => ['required', 'string', 'max:100'],
                'engine_displacement' => ['required', 'numeric', 'min:0'],
                'horsepower' => ['required', 'numeric', 'min:0'],
                'engine_code' => ['required', 'string', 'max:100'],
                'gearbox' => ['required', 'string', 'max:100'],
                'color' => ['required', 'string', 'max:255'],
                'mileage' => ['required', 'numeric', 'min:0'],
                'professional_price' => ['required', 'numeric', 'min:0'],
                'vat_type' => ['required', 'string', 'in:HTVA,TTC'],
                'status' => ['required', 'string'],
                'condition_type' => ['required', 'string', 'in:VN,VO'],
                'user_type' => ['required', 'string', 'in:BE,INT'],
                'creation_date' => ['required', 'date'],
                'gearbox_code' => ['required', 'string', 'max:10'],
            ]);

            if ($validator->fails()) {
                $errors[$itemLabel] = $validator->errors()->all();
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  list<SimpleXMLElement>  $items
     */
    public function syncCars(array $items, CarImport $import): void
    {
        DB::transaction(function () use ($items, $import) {
            $existingActiveIds = Car::query()
                ->where('sync_status', 'active')
                ->pluck('id_produit')
                ->toArray();

            $existingAllIds = Car::query()
                ->pluck('id_produit', 'id')
                ->flip()
                ->toArray();

            $xmlIds = [];
            $newCount = 0;
            $updatedCount = 0;
            $unchangedCount = 0;

            foreach ($items as $item) {
                $idProduit = $this->extractString($item, 'id_produit');
                $xmlIds[] = $idProduit;

                $attributes = $this->mapXmlItemToCarAttributes($item);

                $existingCar = isset($existingAllIds[$idProduit])
                    ? Car::query()->where('id_produit', $idProduit)->first()
                    : null;

                if ($existingCar === null) {
                    $car = Car::query()->create($attributes);
                    $newCount++;
                } elseif ($existingCar->sync_status === 'sold') {
                    $existingCar->update(['last_synced_at' => now()]);
                    $unchangedCount++;
                    $car = $existingCar;
                } else {
                    $hasChanges = $this->carHasChanges($existingCar, $attributes);

                    if ($hasChanges) {
                        $existingCar->update($attributes);
                        $updatedCount++;
                    } else {
                        $existingCar->update([
                            'sync_status' => 'active',
                            'last_synced_at' => now(),
                        ]);
                        $unchangedCount++;
                    }

                    $car = $existingCar;
                }

                $this->syncPhotos($car, $item);
                $this->syncOptions($car, $item);
            }

            $soldIds = array_diff($existingActiveIds, $xmlIds);
            $soldCount = 0;

            if (! empty($soldIds)) {
                $soldCount = Car::query()
                    ->whereIn('id_produit', $soldIds)
                    ->where('sync_status', 'active')
                    ->update([
                        'sync_status' => 'sold',
                        'last_synced_at' => now(),
                    ]);
            }

            $import->update([
                'new_count' => $newCount,
                'updated_count' => $updatedCount,
                'sold_count' => $soldCount,
                'unchanged_count' => $unchangedCount,
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function mapXmlItemToCarAttributes(SimpleXMLElement $item): array
    {
        return [
            'id_produit' => $this->extractString($item, 'id_produit'),
            'vin' => $this->extractNullableString($item, 'vin'),
            'make' => $this->extractString($item, 'marque'),
            'model' => $this->extractString($item, 'modele'),
            'trim_level' => $this->extractString($item, 'finition'),
            'fuel_type' => $this->extractString($item, 'carburant'),
            'engine_displacement' => $this->extractInt($item, 'cylindre'),
            'horsepower' => $this->extractInt($item, 'puissance'),
            'engine_code' => $this->extractString($item, 'moteur'),
            'weight' => $this->extractNullableInt($item, 'poids'),
            'manufacturing_year' => $this->extractNullableInt($item, 'annee_fabrication'),
            'gearbox' => $this->extractString($item, 'boite'),
            'gearbox_code' => $this->extractString($item, 'code_boite'),
            'color' => $this->extractString($item, 'couleur'),
            'color_code' => $this->extractNullableString($item, 'code_couleur'),
            'mileage' => $this->extractInt($item, 'km'),
            'co2' => $this->extractNullableInt($item, 'co2'),
            'co2_wltp' => $this->extractNullableInt($item, 'co2wltp'),
            'wltp_electric_range' => $this->extractNullableInt($item, 'wltp_erange'),
            'euro_standard' => $this->extractNullableString($item, 'norme_euro'),
            'professional_price' => $this->extractFloat($item, 'prix_pro'),
            'vat_type' => $this->extractString($item, 'tva'),
            'status' => $this->parseStatus($this->extractString($item, 'statut')),
            'registration_date' => $this->extractNullableDate($item, 'date_immat'),
            'retention_date' => $this->extractNullableDate($item, 'retention'),
            'warranty_start_date' => $this->extractNullableDate($item, 'date_depart_garantie'),
            'doors' => $this->extractNullableInt($item, 'portes'),
            'category' => $this->extractNullableString($item, 'categorie'),
            'catalogue_base_price_excl_vat' => $this->extractNullableFloat($item, 'prix_base_cat_fr_htva'),
            'catalogue_total_price_excl_vat' => $this->extractNullableFloat($item, 'prix_tot_cat_fr_htva'),
            'catalogue_price_incl_vat' => $this->extractNullableFloat($item, 'prix_catalogue_fr_ttc'),
            'used_car_fees' => $this->extractFloat($item, 'frais_vo', 0.0),
            'used_car_fees_detail' => $this->extractNullableText($item, 'frais_vo_detail'),
            'condition_type' => $this->extractString($item, 'vn_vo'),
            'france_discount' => $this->extractFloat($item, 'remise_france', 0.0),
            'catalogue_model_name' => $this->extractNullableString($item, 'modele_catalogue_fr'),
            'is_clearance' => $this->extractString($item, 'destockage') === 'OUI',
            'argus_price' => $this->extractNullableFloat($item, 'prix_argus'),
            'previous_price' => $this->extractNullableFloat($item, 'ancien_prix'),
            'user_type' => $this->extractString($item, 'user_type'),
            'is_new' => $this->extractString($item, 'new') === 'OUI',
            'catalogue_remark' => $this->extractNullableText($item, 'remarque_catalogue'),
            'creation_date' => $this->extractString($item, 'date_crea'),
            'private_price_incl_vat' => $this->extractFloat($item, 'prix_part_tvac', 0.0),
            'publication_codes' => $this->extractString($item, 'codes_pub', ''),
            'tags' => $this->extractString($item, 'tags', ''),
            'main_equipment_codes' => $this->parseMainEquipmentCodes($item),
            'carlab' => $this->extractString($item, 'carlab') === '1',
            'carpass_url' => $this->extractNullableString($item, 'carpass_url'),
            'ecological_penalty' => $this->extractNullableFloat($item, 'malus'),
            'vehicle_condition' => $this->extractNullableString($item, 'etat'),
            'auction_data' => $this->parseAuctionData($item),
            'stock_level' => $this->extractNullableInt($item, 'stock'),
            'okcars' => $this->extractString($item, 'okcars') === '1',
            'ecarlux' => $this->extractString($item, 'ecarlux') === '1',
            'publish_platforms' => $this->parsePublishPlatforms($item),
            'excluded_countries' => $this->parseExcludedCountries($item),
            'supplementary_equipment' => $this->parseEquipment($item, 'equipments_supplementaires', 'equipments_supplementaires_'),
            'standard_equipment' => $this->parseEquipment($item, 'equipments_serie', 'equipments_serie_'),
            'sync_status' => 'active',
            'last_synced_at' => now(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function parseStatus(string $rawStatus): array
    {
        $parts = explode('%%', $rawStatus);

        $status = [];
        foreach (self::STATUS_LANGUAGE_MAP as $index => $lang) {
            $status[$lang] = isset($parts[$index]) ? trim($parts[$index]) : '';
        }

        return $status;
    }

    /**
     * @return array<string, list<string>>
     */
    public function parseEquipment(SimpleXMLElement $item, string $parentNode, string $childPrefix): array
    {
        $equipment = [];
        $processedLanguages = [];

        if (! isset($item->{$parentNode})) {
            return $equipment;
        }

        $parent = $item->{$parentNode};

        foreach (self::EQUIPMENT_LANGUAGES as $lang) {
            $nodeName = $childPrefix.$lang;

            if (isset($processedLanguages[$lang])) {
                continue;
            }

            if (! isset($parent->{$nodeName})) {
                continue;
            }

            $items = [];
            foreach ($parent->{$nodeName} as $langNode) {
                if (isset($processedLanguages[$lang])) {
                    break;
                }

                foreach ($langNode->equipment as $eq) {
                    $value = trim((string) $eq);
                    if ($value !== '') {
                        $items[] = $value;
                    }
                }

                $processedLanguages[$lang] = true;
            }

            if (! empty($items)) {
                $equipment[$lang] = $items;
            }
        }

        return $equipment;
    }

    private function syncPhotos(Car $car, SimpleXMLElement $item): void
    {
        CarPhoto::query()->where('car_id', $car->id)->delete();

        if (! isset($item->photos->photo)) {
            return;
        }

        $photos = [];
        $position = 0;
        $now = now();

        foreach ($item->photos->photo as $photo) {
            $url = trim((string) $photo);
            if ($url !== '') {
                $photos[] = [
                    'car_id' => $car->id,
                    'url' => $url,
                    'position' => $position++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($photos)) {
            CarPhoto::query()->insert($photos);
        }
    }

    private function syncOptions(Car $car, SimpleXMLElement $item): void
    {
        CarOption::query()->where('car_id', $car->id)->delete();

        if (! isset($item->options_catalogue_fr->option)) {
            return;
        }

        $options = [];
        $now = now();

        foreach ($item->options_catalogue_fr->option as $option) {
            $name = trim((string) $option->nom);
            $price = trim((string) $option->prix);

            if ($name !== '') {
                $options[] = [
                    'car_id' => $car->id,
                    'name' => $name,
                    'price' => is_numeric($price) ? (float) $price : 0.0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($options)) {
            CarOption::query()->insert($options);
        }
    }

    /**
     * @param  array<string, mixed>  $newAttributes
     */
    private function carHasChanges(Car $car, array $newAttributes): bool
    {
        $fieldsToCompare = [
            'vin', 'make', 'model', 'trim_level', 'fuel_type',
            'engine_displacement', 'horsepower', 'engine_code', 'weight',
            'manufacturing_year', 'gearbox', 'gearbox_code', 'color',
            'color_code', 'mileage', 'co2', 'co2_wltp', 'wltp_electric_range',
            'euro_standard', 'professional_price', 'vat_type', 'doors',
            'category', 'catalogue_base_price_excl_vat',
            'catalogue_total_price_excl_vat', 'catalogue_price_incl_vat',
            'used_car_fees', 'condition_type', 'france_discount',
            'catalogue_model_name', 'is_clearance', 'previous_price',
            'user_type', 'is_new', 'private_price_incl_vat',
            'publication_codes', 'stock_level',
        ];

        foreach ($fieldsToCompare as $field) {
            if (! array_key_exists($field, $newAttributes)) {
                continue;
            }

            $oldValue = $car->getAttribute($field);
            $newValue = $newAttributes[$field];

            if ($this->normalizeForComparison($oldValue) !== $this->normalizeForComparison($newValue)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeForComparison(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_float($value) || is_int($value) || (is_string($value) && is_numeric($value))) {
            return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
        }

        return (string) $value;
    }

    /**
     * @return list<int>
     */
    private function parseMainEquipmentCodes(SimpleXMLElement $item): array
    {
        $codes = [];

        if (! isset($item->equipments_principaux->equipment)) {
            return $codes;
        }

        foreach ($item->equipments_principaux->equipment as $eq) {
            $value = trim((string) $eq);
            if ($value !== '' && is_numeric($value)) {
                $codes[] = (int) $value;
            }
        }

        return $codes;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseAuctionData(SimpleXMLElement $item): ?array
    {
        if (! isset($item->auction)) {
            return null;
        }

        $auction = $item->auction;
        $datetimeEnd = trim((string) ($auction->datetime_end ?? ''));
        $minPrice = trim((string) ($auction->min_price ?? ''));
        $prixReserve = trim((string) ($auction->prix_reserve ?? ''));
        $description = trim((string) ($auction->description ?? ''));

        if ($datetimeEnd === '-0-0 0:0:0') {
            $datetimeEnd = '';
        }

        if ($datetimeEnd === '' && $minPrice === '' && $prixReserve === '' && $description === '') {
            return null;
        }

        return [
            'datetime_end' => $datetimeEnd !== '' ? $datetimeEnd : null,
            'min_price' => $minPrice !== '' ? (float) $minPrice : null,
            'prix_reserve' => $prixReserve !== '' ? (float) $prixReserve : null,
            'description' => $description !== '' ? $description : null,
        ];
    }

    /**
     * @return list<string>
     */
    private function parsePublishPlatforms(SimpleXMLElement $item): array
    {
        $platforms = [];

        if (! isset($item->publish->platform)) {
            return $platforms;
        }

        foreach ($item->publish->platform as $platform) {
            $value = trim((string) $platform);
            if ($value !== '') {
                $platforms[] = $value;
            }
        }

        return $platforms;
    }

    /**
     * @return list<string>
     */
    public function parseExcludedCountries(SimpleXMLElement $item): array
    {
        $countries = [];

        if (! isset($item->exclude->pays)) {
            return $countries;
        }

        foreach ($item->exclude->pays as $pays) {
            $value = trim((string) $pays);
            if ($value !== '') {
                $countries[] = $value;
            }
        }

        return $countries;
    }

    private function extractString(SimpleXMLElement $item, string $field, string $default = ''): string
    {
        if (! isset($item->{$field})) {
            return $default;
        }

        $value = trim((string) $item->{$field});

        return $value !== '' ? $value : $default;
    }

    private function extractNullableString(SimpleXMLElement $item, string $field): ?string
    {
        if (! isset($item->{$field})) {
            return null;
        }

        $value = trim((string) $item->{$field});

        return $value !== '' ? $value : null;
    }

    private function extractNullableText(SimpleXMLElement $item, string $field): ?string
    {
        if (! isset($item->{$field})) {
            return null;
        }

        $value = trim((string) $item->{$field});

        if ($value === '' || $value === 'NA') {
            return null;
        }

        return $value;
    }

    private function extractInt(SimpleXMLElement $item, string $field, int $default = 0): int
    {
        $value = trim((string) ($item->{$field} ?? ''));

        return $value !== '' && is_numeric($value) ? (int) $value : $default;
    }

    private function extractNullableInt(SimpleXMLElement $item, string $field): ?int
    {
        $value = trim((string) ($item->{$field} ?? ''));

        return $value !== '' && is_numeric($value) ? (int) $value : null;
    }

    private function extractFloat(SimpleXMLElement $item, string $field, float $default = 0.0): float
    {
        $value = trim((string) ($item->{$field} ?? ''));

        return $value !== '' && is_numeric($value) ? (float) $value : $default;
    }

    private function extractNullableFloat(SimpleXMLElement $item, string $field): ?float
    {
        $value = trim((string) ($item->{$field} ?? ''));

        return $value !== '' && is_numeric($value) ? (float) $value : null;
    }

    private function extractNullableDate(SimpleXMLElement $item, string $field): ?string
    {
        $value = trim((string) ($item->{$field} ?? ''));

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
