<?php

namespace App\Enums;

enum ChamberType: string
{
    case Government = 'govt';
    case Private = 'private';
    case Npo = 'npo';
}
