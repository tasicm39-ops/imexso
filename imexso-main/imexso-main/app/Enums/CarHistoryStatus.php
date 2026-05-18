<?php

namespace App\Enums;

enum CarHistoryStatus: string
{
    case Imported = 'IMPORTED';
    case Available = 'AVAILABLE';
    case Sold = 'SOLD';
}
