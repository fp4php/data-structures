<?php

namespace Fp\DataStructures\Hamt;

use Fp\DataStructures\BitOps;
use Fp\DataStructures\SplFixedArrayOps;
use SplFixedArray;

/**
 * @template TKey
 * @template TValue
 * @extends AbstractNode<TKey, TValue>
 */
final class BitmapNode extends AbstractNode {

    protected string $tag = 'BITMAP';

    /**
     * @param int $bitmap
     * @param SplFixedArray<AbstractNode<TKey, TValue>> $children
     */
    public function __construct(
        public readonly int $bitmap,
        public readonly SplFixedArray $children
    ) { }

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @param TValue $value
     * @return AbstractNode<TKey, TValue>
     */
    public function updated(int $shift, int $hash, mixed $key, mixed $value): AbstractNode
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

    /**
     * @param int $at
     * @param AbstractNode<TKey, TValue> $child
     * @return ArrayNode<TKey, TValue>
     */
    private function expand(int $at, AbstractNode $child): ArrayNode
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
}