<?php

declare(strict_types=1);

namespace Tests;

use Codeception\Test\Unit as CodeceptionUnit;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends CodeceptionUnit
{
    use DatabaseTransactions;
}
