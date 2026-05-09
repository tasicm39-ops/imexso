<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Offre Véhicule - {{ $car->make }} {{ $car->model }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.4; }

        .page-break { page-break-after: always; }

        .header { background: #ffffff; border-bottom: 2px solid #002833; padding: 15px 20px; text-align: center; margin-bottom: 20px; }
        .header .company-name { font-size: 20px; font-weight: bold; color: #002833; }
        .header .company-slogan { font-size: 12px; color: #555; margin-top: 4px; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #002833; color: #ffffff; text-align: center; padding: 8px 15px; font-size: 9px; line-height: 1.5; }

        .bloc-title { background: #002833; color: #ffffff; text-align: center; font-size: 16px; padding: 8px 15px; border-radius: 5px 5px 0 0; font-weight: bold; }
        .bloc-content { border: 2px solid #002833; border-top: none; border-radius: 0 0 5px 5px; padding: 15px; }

        .car-header { border: 2px solid #002833; border-radius: 5px; padding: 12px; margin-bottom: 15px; }

        .badge { display: inline-block; font-size: 14px; font-weight: bold; text-align: center; padding: 4px 15px; border-radius: 12px; margin-bottom: 8px; }
        .badge-vo { background: #add8e6; }
        .badge-vn { background: #ffff00; }

        table.specs { width: 100%; border-collapse: collapse; }
        table.specs td.label { font-weight: bold; font-size: 12px; padding: 6px 10px 6px 0; border-bottom: 1px solid #ddd; width: 40%; }
        table.specs td.value { text-align: right; font-size: 12px; padding: 6px 0 6px 30px; border-bottom: 1px solid #ddd; }

        table.devis { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.devis th { background: #add8e6; color: #fff; padding: 7px; text-align: left; font-size: 11px; }
        table.devis td { padding: 7px; font-size: 11px; }
        table.devis .total-row td { background: #afafaf; color: #fff; font-weight: bold; }
        table.devis .grand-total-row td { background: #002833; color: #fff; font-weight: bold; font-size: 13px; }

        .equipment-list { list-style: none; padding: 0; }
        .equipment-list li { padding: 3px 0; font-size: 11px; line-height: 1.6; }

        .signature-block { margin-top: 30px; }
        .signature-block p { font-size: 12px; margin-bottom: 5px; }

        .section-title { font-size: 14px; font-weight: bold; color: #002833; margin-bottom: 10px; }
        .validity-notice { margin-top: 8px; font-size: 10px; }
        .legal-notice { color: red; margin-top: 8px; font-size: 10px; }
    </style>
</head>
<body>

{{-- PAGE 1: Vehicle Sheet --}}
<div class="header">
    @if($logoBase64 ?? false)
        <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" style="max-height: 80px; display: block; margin: 0 auto;" alt="{{ $user->company_name ?? $user->name }}">
        @if($user->slogan)
            <div class="company-slogan" style="font-weight: bold; margin-top: 6px;">{{ strtoupper($user->slogan) }}</div>
        @endif
    @else
        <div class="company-name">{{ $user->company_name ?? $user->name }}</div>
        @if($user->slogan)
            <div class="company-slogan">{{ $user->slogan }}</div>
        @endif
    @endif
</div>

<div style="margin: 0 20px 80px 20px;">
    <div class="car-header">
        <span class="badge {{ $car->condition_type === 'VN' ? 'badge-vn' : 'badge-vo' }}">
            {{ $car->condition_type }}
        </span>
        <h1 style="font-size: 20px; margin: 5px 0;">{{ $car->make }} {{ $car->model }}</h1>
        <p style="font-size: 13px; color: #555;">{{ $car->trim_level }}</p>
        @if($car->id_produit)
            <p style="font-size: 10px; color: #888; margin-top: 4px;">Ref: {{ $car->id_produit }}</p>
        @endif
    </div>

    @if($photos->isNotEmpty())
        <div style="text-align: center; margin-bottom: 15px;">
            <img src="{{ $photos->first()->url }}" style="max-width: 100%; max-height: 250px;" alt="{{ $car->make }} {{ $car->model }}">
        </div>
    @endif

    <div class="bloc-title">CARACTÉRISTIQUES</div>
    <div class="bloc-content">
        <table class="specs">
            <tr><td class="label">Marque</td><td class="value">{{ $car->make }}</td></tr>
            <tr><td class="label">Modèle</td><td class="value">{{ $car->model }}</td></tr>
            <tr><td class="label">Finition</td><td class="value">{{ $car->trim_level }}</td></tr>
            <tr><td class="label">Carburant</td><td class="value">{{ $car->fuel_type }}</td></tr>
            <tr><td class="label">Puissance</td><td class="value">{{ $car->horsepower }} HP</td></tr>
            <tr><td class="label">Cylindrée</td><td class="value">{{ $car->engine_displacement }}</td></tr>
            <tr><td class="label">Boîte</td><td class="value">{{ $car->gearbox }}</td></tr>
            <tr><td class="label">Couleur</td><td class="value">{{ $car->color }}</td></tr>
            <tr><td class="label">Kilométrage</td><td class="value">{{ number_format($car->mileage, 0, ',', '.') }} km</td></tr>
            <tr><td class="label">Année</td><td class="value">{{ $car->manufacturing_year }}</td></tr>
            <tr><td class="label">Portes</td><td class="value">{{ $car->doors ?? 'N/A' }}</td></tr>
            @if($car->co2)
                <tr><td class="label">CO2</td><td class="value">{{ $car->co2 }} g/km</td></tr>
            @endif
            <tr>
                <td class="label">VIN</td>
                <td class="value">
                    <a href="{{ $frontendContactUrl }}">Contactez-nous</a>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="footer">
    <strong>{{ $user->company_name ?? $user->name }}</strong><br>
    {{ $user->address }}
    @if($user->postal_code || $user->city) - {{ $user->postal_code }} {{ $user->city }}@endif<br>
    @if($user->phone){{ $user->phone }} - @endif{{ $user->email }}
    @if($user->vat_number)<br>TVA {{ $user->vat_number }}@endif
</div>

<div class="page-break"></div>

{{-- PAGE 2: Equipment & Options --}}
<div class="header">
    @if($logoBase64 ?? false)
        <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" style="max-height: 80px; display: block; margin: 0 auto;" alt="{{ $user->company_name ?? $user->name }}">
    @else
        <div class="company-name">{{ $user->company_name ?? $user->name }}</div>
    @endif
</div>

<div style="margin: 0 20px 80px 20px;">
    @if(!empty($supplementaryEquipment) && count($supplementaryEquipment) > 0)
        <div class="bloc-title">OPTIONS / ÉQUIPEMENT SUPPLÉMENTAIRE</div>
        <div class="bloc-content">
            <ul class="equipment-list">
                @foreach($supplementaryEquipment as $item)
                    <li>{{ is_array($item) ? ($item['name'] ?? $item[0] ?? '') : $item }}</li>
                @endforeach
            </ul>
        </div>
        <br>
    @endif

    @if($options->isNotEmpty())
        <div class="bloc-title">OPTIONS</div>
        <div class="bloc-content">
            <ul class="equipment-list">
                @foreach($options as $option)
                    <li>{{ $option->name }}</li>
                @endforeach
            </ul>
        </div>
        <br>
    @endif

    @if(!empty($standardEquipment) && count($standardEquipment) > 0)
        <div class="bloc-title">ÉQUIPEMENTS DE SÉRIE</div>
        <div class="bloc-content">
            <ul class="equipment-list">
                @foreach($standardEquipment as $item)
                    <li>{{ is_array($item) ? ($item['name'] ?? $item[0] ?? '') : $item }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<div class="footer">
    <strong>{{ $user->company_name ?? $user->name }}</strong><br>
    {{ $user->address }}
    @if($user->postal_code || $user->city) - {{ $user->postal_code }} {{ $user->city }}@endif<br>
    @if($user->phone){{ $user->phone }} - @endif{{ $user->email }}
    @if($user->vat_number)<br>TVA {{ $user->vat_number }}@endif
</div>

<div class="page-break"></div>

{{-- PAGE 3: Devis (Quote) --}}
<div class="header">
    @if($logoBase64 ?? false)
        <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" style="max-height: 80px; display: block; margin: 0 auto;" alt="{{ $user->company_name ?? $user->name }}">
    @else
        <div class="company-name">{{ $user->company_name ?? $user->name }}</div>
    @endif
</div>

<div style="margin: 0 20px 80px 20px;">
    <div class="bloc-title">DEVIS</div>
    <div class="bloc-content">
        @if($offer->client_name)
            <p style="margin-bottom: 10px;"><strong>Client :</strong> {{ $offer->client_name }}
                @if($offer->client_email) - {{ $offer->client_email }}@endif
            </p>
        @endif

        <table class="devis">
            <thead>
                <tr>
                    <th style="width: 40%;">Désignation</th>
                    <th style="width: 15%; text-align: right;">P.U. HT</th>
                    <th style="width: 10%; text-align: right;">Qté</th>
                    <th style="width: 15%; text-align: right;">TVA</th>
                    <th style="width: 20%; text-align: right;">Total HT</th>
                </tr>
            </thead>
            <tbody>
                {{-- Vehicle line --}}
                <tr>
                    <td>{{ $car->make }} {{ $car->model }} {{ $car->trim_level }}</td>
                    <td style="text-align: right;">{{ number_format($offer->price_excl_vat, 2, ',', '.') }} €</td>
                    <td style="text-align: right;">1</td>
                    <td style="text-align: right;">{{ $offer->vat_rate > 0 ? number_format($offer->vat_rate, 0) . '%' : 'TTC' }}</td>
                    <td style="text-align: right;">{{ number_format($offer->price_excl_vat, 2, ',', '.') }} €</td>
                </tr>

                {{-- Setup fees --}}
                @if($offer->setup_fees > 0)
                    <tr>
                        <td>FRAIS DE MISE EN ROUTE (HTVA)</td>
                        <td style="text-align: right;">{{ number_format($offer->setup_fees, 2, ',', '.') }} €</td>
                        <td style="text-align: right;">1</td>
                        <td style="text-align: right;">{{ $offer->vat_rate > 0 ? number_format($offer->vat_rate, 0) . '%' : 'TTC' }}</td>
                        <td style="text-align: right;">{{ number_format($offer->setup_fees, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                {{-- Total HTVA --}}
                @php
                    $totalHtva = $offer->price_excl_vat + $offer->setup_fees;
                @endphp
                <tr class="total-row">
                    <td>TOTAL HTVA</td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;">{{ number_format($totalHtva, 2, ',', '.') }} €</td>
                </tr>

                {{-- TVA amount --}}
                @php
                    $tvaCar = $offer->price_incl_vat - $offer->price_excl_vat;
                    $tvaSetupFees = $offer->vat_rate > 0 ? ($offer->setup_fees * $offer->vat_rate / 100) : 0;
                    $totalTva = $tvaCar + $tvaSetupFees;
                @endphp
                <tr class="total-row">
                    <td>MONTANT TVA</td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;">{{ number_format($totalTva, 2, ',', '.') }} €</td>
                </tr>

                {{-- Additional fees (not subject to VAT) --}}
                @if($offer->registration_fees > 0)
                    <tr>
                        <td>FRAIS CARTE GRISE</td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;">{{ number_format($offer->registration_fees, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                @if($offer->admin_fees > 0)
                    <tr>
                        <td>FRAIS DE GESTION</td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;">{{ number_format($offer->admin_fees, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                @if($offer->bonus_malus != 0)
                    <tr>
                        <td>BONUS / MALUS</td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;">{{ number_format($offer->bonus_malus, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                @if($offer->ww_fees > 0)
                    <tr>
                        <td>WW</td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;">{{ number_format($offer->ww_fees, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                {{-- Total non-VAT fees --}}
                @php
                    $totalNonVatFees = $offer->registration_fees + $offer->admin_fees + $offer->bonus_malus + $offer->ww_fees;
                @endphp
                @if($totalNonVatFees != 0)
                    <tr class="total-row">
                        <td>TOTAL FRAIS NON SOUMIS À TVA</td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;"></td>
                        <td style="text-align: right;">{{ number_format($totalNonVatFees, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                {{-- Grand total TTC --}}
                @php
                    $grandTotal = $totalHtva + $totalTva + $totalNonVatFees;
                @endphp
                <tr class="grand-total-row">
                    <td>TOTAL TTC</td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;">{{ number_format($grandTotal, 2, ',', '.') }} €</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($offer->validity_days)
        <p class="validity-notice">Durée de validité de l'offre : {{ $offer->validity_days }} jour(s)</p>
    @endif

    <p class="legal-notice">Les normes anti-pollution peuvent impacter le coût final du véhicule.</p>

    @if($offer->message)
        <div class="car-header" style="margin-top: 15px;">
            <p style="font-size: 11px;">{{ $offer->message }}</p>
        </div>
    @endif

    <div class="signature-block">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    <p style="font-size: 13px; margin-bottom: 25px;"><strong>Signature pour accord</strong></p>
                    <p>Fait à .....................................<br>Le ........................................</p>
                    <p style="margin-top: 15px;">Bon pour accord</p>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="footer">
    <strong>{{ $user->company_name ?? $user->name }}</strong><br>
    {{ $user->address }}
    @if($user->postal_code || $user->city) - {{ $user->postal_code }} {{ $user->city }}@endif<br>
    @if($user->phone){{ $user->phone }} - @endif{{ $user->email }}
    @if($user->vat_number)<br>TVA {{ $user->vat_number }}@endif
</div>

</body>
</html>
