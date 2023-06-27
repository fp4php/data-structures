<?php

declare(strict_types=1);

namespace Hamt;

use Fp\DataStructures\BitOps;
use PHPUnit\Framework\TestCase;

final class BitOpsTest extends TestCase
{
    public function testPopcount(): void
    {
        $this->assertEquals(4, BitOps::popcount(0b1010101));
        $this->assertEquals(0, BitOps::popcount(0b0000000));
    }

    public function testUnsignedRightShift(): void
    {
        $this->assertEquals(0b11101_10010_11110, BitOps::unsignedRightShift(0b11101_10010_11110, 0));
        $this->assertEquals(0b11101_10010, BitOps::unsignedRightShift(0b11101_10010_11110, 5));
        $this->assertEquals(0b11101, BitOps::unsignedRightShift(0b11101_10010_11110, 10));
        $this->assertEquals(0b0, BitOps::unsignedRightShift(0b11101_10010_11110, 15));
    }

    public function testHashFragment(): void
    {
        $this->assertEquals(0b11110, BitOps::hashFragment(0, 0b11101_10010_11110));
        $this->assertEquals(0b10010, BitOps::hashFragment(5, 0b11101_10010_11110));
        $this->assertEquals(0b11101, BitOps::hashFragment(10, 0b11101_10010_11110));
        $this->assertEquals(0b0, BitOps::hashFragment(15, 0b11101_10010_11110));
    }

    public function testIndexFromBitmap(): void
    {
        $this->assertEquals(4, BitOps::indexFromBitmap(0b00001111, 1 << 4));
        $this->assertEquals(3, BitOps::indexFromBitmap(0b00001101, 1 << 4));
        $this->assertEquals(4, BitOps::indexFromBitmap(0b11111111, 1 << 4));
        $this->assertEquals(3, BitOps::indexFromBitmap(0b11111101, 1 << 4));
    }

    public function testNumberOfLeadingZeros(): void
    {
        $this->assertEquals(64, BitOps::numberOfLeadingZeros(0b0));
        $this->assertEquals(62, BitOps::numberOfLeadingZeros(0b11));
    }

    public function testHighestOneBit(): void
    {
        $this->assertEquals(0b1000000, BitOps::highestOneBit(0b1010101));
        $this->assertEquals(0, BitOps::highestOneBit(0b0));
    }
}
