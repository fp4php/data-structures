<?php

namespace Fp\DataStructures;

use SplFixedArray;

class SplFixedArrayOps
{
    /**
     * Create new array of the same size with updated value at given position
     *
     * @template TValue
     * @param int $at
     * @param TValue $val
     * @param SplFixedArray<TValue> $arr
     * @return SplFixedArray<TValue>
     */
    public static function arrayUpdate(int $at, mixed $val, SplFixedArray $arr): SplFixedArray {
        $len = $arr->getSize();
        $out = new SplFixedArray($len);

        for ($i = 0; $i < $len; ++$i) {
            $out[$i] = $arr[$i];
        }

        $out[$at] = $val;

        return $out;
    }

    /**
     * Create new array with element at given position removed
     *
     * @template TValue
     * @param int $at
     * @param SplFixedArray<TValue> $arr
     * @return SplFixedArray<TValue>
     */
    public static function arraySpliceOut(int $at, SplFixedArray $arr): SplFixedArray {
        $len = $arr->getSize();
        $out = new SplFixedArray($len - 1);
        $i = $j = 0;

        while ($i < $at) {
            $out[$j++] = $arr[$i++];
        }

        ++$i;

        while ($i < $len) {
            $out[$j++] = $arr[$i++];
        }

        return $out;
    }

    /**
     * Create new array with element at given position inserted
     *
     * @template TValue
     * @param int $at
     * @param TValue $val
     * @param SplFixedArray<TValue> $arr
     * @return SplFixedArray<TValue>
     */
    public static function arraySpliceIn(int $at, mixed $val, SplFixedArray $arr): SplFixedArray {
        $len = $arr->getSize();
        $out = new SplFixedArray($len + 1);
        $i = $j = 0;

        while ($i < $at) {
            $out[$j++] = $arr[$i++];
        }

        $out[$j++] = $val;

        while ($i < $len) {
            $out[$j++] = $arr[$i++];
        }

        return $out;
    }
}