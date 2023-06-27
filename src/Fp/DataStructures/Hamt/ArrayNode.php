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
final class ArrayNode extends HashArrayMappedTrie {

    protected string $tag = 'ARRAY';

    /**
     * @param int $size
     * @param SplFixedArray<HashArrayMappedTrie<TKey, TValue>> $children
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
     * @return HashArrayMappedTrie<TKey, TValue>
     */
    public function updated(int $shift, int $hash, mixed $key, mixed $value): HashArrayMappedTrie
    {
        $count = $this->size;
        $children = $this->children;
        $frag = BitOps::hashFragment($shift, $hash);
        $oldChild = $children[$frag];

        $newChild = $oldChild
            ? $oldChild->updated($shift + 5, $hash, $key, $value)
            : new LeafNode($hash, $key, $value);

        if ($oldChild === $newChild) {
            return $this;
        }

        if (!$oldChild && $newChild) { // add
            return new ArrayNode(
                $count + 1,
                SplFixedArrayOps::arrayUpdate($frag, $newChild, $children)
            );
        }

        // modify
        return new ArrayNode($count, SplFixedArrayOps::arrayUpdate($frag, $newChild, $children));
    }
}