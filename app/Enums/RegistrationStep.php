<?php

declare(strict_types=1);

namespace App\Enums;

enum RegistrationStep: int
{
    case STEP1 = 1;
    case STEP2 = 2;
    case STEP3 = 3;
    case STEP4 = 4;
    case STEP5 = 5;
}
