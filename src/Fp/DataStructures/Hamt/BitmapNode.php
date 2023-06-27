<?php

namespace Fp\DataStructures\Hamt;

use Fp\DataStructures\BitOps;
use Fp\DataStructures\SplFixedArrayOps;
use SplFixedArray;

/**
 * @template TKey
 * @template TValue
 * @extends HashArrayMappedTrie<TKey, TValue>
 */
final class BitmapNode extends HashArrayMappedTrie {

    protected string $tag = 'BITMAP';

    /**
     * @param int $bitmap
     * @param SplFixedArray<HashArrayMappedTrie<TKey, TValue>> $children
     */
    public function __construct(
        public readonly int $bitmap,
        public readonly SplFixedArray $children
    ) { }

    /**
     * @param int $at
     * @param HashArrayMappedTrie<TKey, TValue> $child
     * @return ArrayNode<TKey, TValue>
     */
    private function expand(int $at, HashArrayMappedTrie $child): ArrayNode
    {
        $len = PHP_INT_SIZE * 8 - BitOps::numberOfLeadingZeros($this->bitmap | (1 << $at));
        $arr = new SplFixedArray($len);
        $bit = $this->bitmap;
        $count = 0;

        for ($i = 0; $bit; ++$i) {
            if ($bit & 1) {
                $arr[$i] = $this->children[$count++];
            }
            $bit = BitOps::unsignedRightShift($bit, 1);
        }

        $arr[$at] = $child;

        return new ArrayNode($count + 1, $arr);
    }

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @param TValue $value
     * @return HashArrayMappedTrie<TKey, TValue>
     */
    public function updated(int $shift, int $hash, mixed $key, mixed $value): HashArrayMappedTrie
    {
        $bitmap = $this->bitmap;
        $children = $this->children;
        $frag = BitOps::hashFragment($shift, $hash);
        $bit = 1 << $frag;
        $idx = BitOps::indexFromBitmap($bitmap, $bit);
        $exists = $bitmap & $bit;

        if ($exists) {
            // modify
            $current = $children[$idx];
            $newChild = $current->updated($shift + 5, $hash, $key, $value);

            if ($current === $newChild) {
                return $this;
            }

            return new BitmapNode($bitmap, SplFixedArrayOps::arrayUpdate($idx, $newChild, $children));

        } else {
            // add
            $newChild = new LeafNode($hash, $key, $value);
            return $this->children->getSize() >= 16
                ? $this->expand($frag, $newChild)
                : new BitmapNode($bitmap | $bit, SplFixedArrayOps::arraySpliceIn($idx, $newChild, $children));
        }
    }

}