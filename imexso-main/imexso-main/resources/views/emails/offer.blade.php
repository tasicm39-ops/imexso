<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <title>Offre véhicule</title>
    <style>
        body { height: 100% !important; margin: 0; padding: 0; width: 100% !important; }
        table { border-collapse: collapse; border-spacing: 0 !important; }
        img, a img { border: 0; outline: none; text-decoration: none; }
        p { margin: 1em 0; }
        @media only screen and (max-width: 599px) {
            img.pattern { max-width: 100%; height: auto !important; }
        }
        @media only screen and (max-width: 480px) {
            body { width: 100% !important; min-width: 100% !important; }
            .responsive_block { width: 100% !important; min-width: 100% !important; }
        }
    </style>
</head>
<body style="background: #F5F5F5; padding-top: 50px; padding-bottom: 50px;">
    <table cellpadding="0" cellspacing="0" border="0" height="100%" width="100%" style="width: 100%;">
        <tr>
            <td align="center" valign="top">
                {{-- Header --}}
                <table class="responsive_block" cellpadding="0" cellspacing="0" border="0" width="600" style="margin-top: 0;">
                    <tr>
                        <td bgcolor="#ffffff" valign="middle" align="center" style="background: #ffffff; border-bottom: solid 1px #000000; padding: 20px 15px;">
                            @if($logoUrl ?? false)
                                <img class="pattern" src="{{ $logoUrl }}" alt="{{ $sender->company_name ?? $sender->name }}" width="180" height="auto" style="display: block; margin: 0 auto; border: 0;" />
                            @else
                                <p style="color: #000000; font-weight: bold; text-align: center; font-size: 18px; margin: 0;">
                                    {{ $sender->company_name ?? $sender->name }}
                                </p>
                            @endif
                            @if($sender->slogan)
                                <p style="color: #555555; font-weight: normal; text-align: center; font-size: 13px; margin: 5px 0 0 0;">
                                    {{ $sender->slogan }}
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- Body --}}
                <table class="responsive_block" cellpadding="0" cellspacing="0" border="0" width="600" style="margin-top: 0;">
                    <tr>
                        <td bgcolor="#ffffff" align="left" valign="top" style="text-align: left; background: #ffffff; padding: 40px;">
                            <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">Cher Client,</p>

                            <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                                Veuillez trouver ci-joint une offre pour le véhicule :
                            </p>

                            <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                                <strong>{{ $car->make }} {{ $car->model }}</strong><br>
                                {{ $car->trim_level }}
                                {{ $car->fuel_type }}
                                {{ $car->horsepower }} HP
                                {{ $car->engine_displacement }}
                            </p>

                            <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">Cordialement</p>
                        </td>
                    </tr>
                </table>

                {{-- Footer --}}
                <table bgcolor="#ffffff" class="responsive_block" cellpadding="0" cellspacing="0" border="0" width="600" style="margin-top: 0; background: #002833;">
                    <tr>
                        <td align="center" valign="middle" style="padding: 20px 30px;">
                            <p style="color: #ffffff; text-align: center; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; margin: 0;">
                                <strong>{{ $sender->company_name ?? $sender->name }}</strong><br>
                                @if($sender->address){{ $sender->address }}<br>@endif
                                @if($sender->postal_code || $sender->city){{ $sender->postal_code }} {{ $sender->city }}<br>@endif
                                @if($sender->phone){{ $sender->phone }} - @endif{{ $sender->email }}
                                @if($sender->vat_number)<br>TVA {{ $sender->vat_number }}@endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
