<?php

namespace App\Enums;

enum CarStockStatus: string
{
    case Available = 'AVAILABLE';
    case Sold = 'SOLD';
}
