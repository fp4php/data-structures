<?php

declare(strict_types=1);

namespace Tests;

use Fp\DataStructures\SplFixedArrayOps;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

final class SplFixedArrayOpsTest extends TestCase
{
    public function testArrayAppend(): void
    {
        $arr = new SplFixedArray(3);
        $arr[0] = 0;
        $arr[1] = 1;
        $arr[2] = 2;
        $actual = SplFixedArrayOps::arrayAppend(3, $arr);

        $this->assertEquals([0, 1, 2, 3], $actual->toArray());
    }

    public function testArrayPrepend(): void
    {
        $arr = new SplFixedArray(3);
        $arr[0] = 0;
        $arr[1] = 1;
        $arr[2] = 2;
        $actual = SplFixedArrayOps::arrayPrepend(3, $arr);

        $this->assertEquals([3, 0, 1, 2], $actual->toArray());
    }

}
