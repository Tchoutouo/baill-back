<?php

namespace App\Enums;

enum ProfilCode: int
{
    case SuperAdmin  = 1;
    case Admin       = 2;
    case Advertiser  = 3;
}
