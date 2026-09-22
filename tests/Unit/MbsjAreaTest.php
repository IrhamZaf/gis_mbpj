<?php

namespace Tests\Unit;

use App\Support\MbsjArea;
use PHPUnit\Framework\TestCase;

class MbsjAreaTest extends TestCase
{
    public function test_contains_mbsj_center(): void
    {
        $this->assertTrue(MbsjArea::contains(MbsjArea::CENTER_LAT, MbsjArea::CENTER_LNG));
        $this->assertTrue(MbsjArea::contains(3.07, 101.58));
    }

    public function test_rejects_outside_mbsj(): void
    {
        $this->assertFalse(MbsjArea::contains(3.1579, 101.7117)); // KLCC
        $this->assertFalse(MbsjArea::contains(2.90, 101.58)); // too south
        $this->assertFalse(MbsjArea::contains(null, 101.58));
        $this->assertFalse(MbsjArea::contains(3.05, null));
    }
}
