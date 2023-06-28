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

    public function testSplFixedArrayIterator(): void
    {
        $arr = new SplFixedArray(6);
        $arr[0] = 0;
        $arr[2] = 2;
        $arr[5] = 5;

        $actual = [];
        foreach ($arr as $i => $v) {
            $actual[$i] = $v;
        }

        $expected = [
            0 => 0,
            1 => null,
            2 => 2,
            3 => null,
            4 => null,
            5 => 5,
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testArrayUpdate(): void
    {
        $arr = new SplFixedArray(3);
        $arr[0] = 0;
        $arr[1] = 1;
        $arr[2] = 2;
        $actual = SplFixedArrayOps::arrayUpdate(5, 5, $arr);
        $actual = SplFixedArrayOps::arrayUpdate(2, 22, $actual);

        $this->assertEquals([0, 1, 22, null, null, 5], $actual->toArray());
    }
}
