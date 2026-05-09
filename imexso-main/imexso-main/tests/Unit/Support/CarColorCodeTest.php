<?php

namespace Tests\Unit\Support;

use App\Support\CarColorCode;
use PHPUnit\Framework\TestCase;

class CarColorCodeTest extends TestCase
{
    public function test_numeric_codes_map_to_expected_groups_from_xml_samples(): void
    {
        $this->assertSame('white', CarColorCode::resolveGroupKey('1', 'BLANC KAOLIN'));
        $this->assertSame('black', CarColorCode::resolveGroupKey('2', 'NOIR'));
        $this->assertSame('grey', CarColorCode::resolveGroupKey('3', 'GRIS ARTENSE'));
        $this->assertSame('blue', CarColorCode::resolveGroupKey('4', 'BLEU ECLIPSE'));
        $this->assertSame('red', CarColorCode::resolveGroupKey('5', 'ROUGE'));
        $this->assertSame('green', CarColorCode::resolveGroupKey('6', 'VERT CEDRE'));
        $this->assertSame('yellow', CarColorCode::resolveGroupKey('8', 'JAUNE'));
        $this->assertSame('beige', CarColorCode::resolveGroupKey('12', 'BEIGE SAFARI'));
        $this->assertSame('other', CarColorCode::resolveGroupKey('0', 'SANDSTONE'));
    }

    public function test_missing_color_code_falls_back_to_label_keywords(): void
    {
        $this->assertSame('grey', CarColorCode::resolveGroupKey(null, 'ECOTRONIC GREY'));
        $this->assertSame('other', CarColorCode::resolveGroupKey(null, 'TITANIUM FLASH'));
    }
}
