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

    protected int $tag = 1;

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
        $fragment = BitOps::hashFragment($shift, $hash);
        $bit = 1 << $fragment;
        $idx = BitOps::indexFromBitmap($this->bitmap, $bit);

        if ($this->bitmap & $bit) { // child exists
            $oldChild = $this->children[$idx];
            $newChild = $oldChild->updated($shift + 5, $hash, $key, $value);

            if ($oldChild === $newChild) {
                // no modifications required
                return $this;
            }

            // replace old branch with new updated one
            // no expansion needed because of the same children list size
            $children = SplFixedArrayOps::arrayUpdate($idx, $newChild, $this->children);
            return new BitmapNode($this->bitmap, $children);
        }

        // child doesn't exist
        // children list size will be increased
        // thus decompression to array node may be needed
        $newChild = new LeafNode($hash, $key, $value);
        return $this->children->getSize() >= 16
            ? $this->decompress($fragment, $newChild)
            : new BitmapNode($this->bitmap | $bit, SplFixedArrayOps::arraySpliceIn($idx, $newChild, $this->children));
    }

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @return AbstractNode<TKey, TValue>|null
     */
    public function removed(int $shift, int $hash, mixed $key): ?AbstractNode
    {
        $bit = 1 << BitOps::hashFragment($shift, $hash);

        if (!($this->bitmap & $bit)) {
            return $this;
        }

        // old child exists
        $idx = BitOps::indexFromBitmap($this->bitmap, $bit);
        $oldChild = $this->children[$idx];
        $newChild = $oldChild->removed($shift + 5, $hash, $key);

        if ($newChild == $oldChild) {
            // key not found in the branch
            return $this;
        }

        if (!$newChild) {
            // whole branch is removed
            $bitmap = $this->bitmap & ~$bit;
            return $bitmap
                ? new BitmapNode($bitmap, SplFixedArrayOps::arraySpliceOut($idx, $this->children))
                : null;
        }

        // old branch was modified
        // replace old branch with modified one
        return new BitmapNode(
            $this->bitmap,
            SplFixedArrayOps::arrayUpdate($idx, $newChild, $this->children)
        );
    }

    /**
     * Decompress bitmap to array node and set node at given position
     *
     * @param int $at
     * @param AbstractNode<TKey, TValue> $node
     * @return ArrayNode<TKey, TValue>
     */
    private function decompress(int $at, AbstractNode $node): ArrayNode
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

        $arr[$at] = $node;

        return new ArrayNode($count + 1, $arr);
    }
}