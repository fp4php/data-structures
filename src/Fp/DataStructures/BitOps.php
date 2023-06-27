<?php

namespace Fp\DataStructures;

class BitOps
{
    public static function hash32(int $hash): int
    {
        return PHP_INT_SIZE == 4
            ? $hash
            : ($hash & 0xffffffff) ^ (($hash >> 32) & 0xffffffff);
    }

    // Hamming weight. Count number of 1 bits.
    public static function popcount(int $x): int
    {
        $x -= (($x >> 1) & 0x55555555);
        $x = ($x & 0x33333333) + (($x >> 2) & 0x33333333);
        $x = ($x + ($x >> 4)) & 0x0f0f0f0f;
        $x += ($x >> 8);
        $x += ($x >> 16);
        return ($x & 0x7f);
    }

    // >>> operator analog
    public static function unsignedRightShift(int $n, int $p): int
    {
        if ($p == 0) return $n;
        return ($n >> $p) & ~(1 << (8 * PHP_INT_SIZE - 1) >> ($p - 1));
    }

    // shift hash right and take 5 least significant bits
    public static function hashFragment(int $shift, int $hash): int
    {
        return self::unsignedRightShift($hash, $shift) & 31;
    }

    // given node bitmap and some bit, return corresponding index in node children array
    public static function indexFromBitmap(int $bitmap, int $bit): int
    {
        return self::popcount($bitmap & ($bit - 1));
    }

    public static function highestOneBit(int $i): int
    {
        return $i & self::unsignedRightShift(PHP_INT_MIN, self::numberOfLeadingZeros($i));
    }

    public static function numberOfLeadingZeros(int $i): int
    {
        if (PHP_INT_SIZE == 8) {
            $x = self::unsignedRightShift($i, 32);
            return $x == 0 ? 32 + self::numberOfLeadingZeros32($i) : self::numberOfLeadingZeros32($x);
        } else {
            return self::numberOfLeadingZeros32($i);
        }
    }

    private static function numberOfLeadingZeros32(int $i): int
    {
        if ($i <= 0) {
            return $i == 0 ? 32 : 0;
        }

        $n = 31;

        if ($i >= 1 << 16) { $n -= 16; $i = self::unsignedRightShift($i, 16); }
        if ($i >= 1 <<  8) { $n -=  8; $i = self::unsignedRightShift($i, 8) ; }
        if ($i >= 1 <<  4) { $n -=  4; $i = self::unsignedRightShift($i, 4) ; }
        if ($i >= 1 <<  2) { $n -=  2; $i = self::unsignedRightShift($i, 2) ; }

        return $n - self::unsignedRightShift($i, 1);
    }
}