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
final class ArrayNode extends AbstractNode {

    protected int $tag = 2;

    /**
     * @param int $size
     * @param SplFixedArray<AbstractNode<TKey, TValue>> $children
     */
    public function __construct(
        public readonly int $size,
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
        $hashPart = BitOps::hashFragment($shift, $hash);
        $oldChild = $this->children[$hashPart];

        $newChild = $oldChild
            ? $oldChild->updated($shift + 5, $hash, $key, $value)
            : new LeafNode($hash, $key, $value);

        if ($oldChild === $newChild) {
            return $this;
        }

        if (!$oldChild) { // add
            return new ArrayNode(
                $this->size + 1,
                SplFixedArrayOps::arrayUpdate($hashPart, $newChild, $this->children)
            );
        }

        // modify
        return new ArrayNode(
            $this->size,
            SplFixedArrayOps::arrayUpdate($hashPart, $newChild, $this->children)
        );
    }

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @return AbstractNode<TKey, TValue>|null
     */
    public function removed(int $shift, int $hash, mixed $key): ?AbstractNode
    {
        $hashPart = BitOps::hashFragment($shift, $hash);
        $oldChild = $this->children[$hashPart];

        if (!$oldChild) {
            return $this;
        }

        // old child exists
        $newChild = $oldChild->removed($shift + 5, $hash, $key);

        if ($newChild == $oldChild) {
            // key not found in the branch
            return $this;
        }

        if (!$newChild) {
            // whole branch is removed
            // resized is required
            // may be compressed into the bitmap node
            return $this->size - 1 < 16
                ? $this->compress($hashPart)
                : new ArrayNode(
                    $this->size - 1,
                    SplFixedArrayOps::arrayUpdate($hashPart, null, $this->children)
                );
        }

        // old branch was modified
        // no resize needed
        // replace old branch with modified one
        return new ArrayNode(
            $this->size,
            SplFixedArrayOps::arrayUpdate($hashPart, $newChild, $this->children)
        );
    }

    /**
     * @param int $removedAt
     * @return BitmapNode<TKey, TValue>
     */
    private function compress(int $removedAt): BitmapNode
    {
        $j = 0;
        $bitmap = 0;
        $children = new SplFixedArray($this->size - 1);

        foreach ($this->children as $i => $child) {
            if ($i !== $removedAt && $child) {
                $children[$j++] = $child;
                $bitmap |= 1 << $i;
            }
        }

        return new BitmapNode($bitmap, $children);
    }
}