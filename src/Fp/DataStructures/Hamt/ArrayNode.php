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
}