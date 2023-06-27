<?php

namespace Fp\DataStructures\Hamt;

use Fp\DataStructures\SplFixedArrayOps;
use SplFixedArray;

/**
 * @template TKey
 * @template TValue
 * @extends LeafLikeNode<TKey, TValue>
 */
final class CollisionNode extends LeafLikeNode {

    protected string $tag = 'COLLISION';

    /**
     * @param int $hash
     * @param SplFixedArray<LeafNode<TKey, TValue>> $children
     */
    public function __construct(
        public readonly int $hash,
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
        if ($hash !== $this->hash) {
            return $this->merge($shift, new LeafNode($hash, $key, $value));
        }

        $list = $this->updateCollisionList($this->hash, $this->children, $key, $value);

        if ($list === $this->children) {
            return $this;
        }

        return $list->getSize() > 1
            ? new CollisionNode($this->hash, $list)
            : $list[0]; // collapse single element collision list
    }

    /**
     * @param int $hash
     * @param SplFixedArray<LeafNode> $list
     * @param TKey $key
     * @param TValue $value
     * @return SplFixedArray<LeafNode>
     */
    public function updateCollisionList(int $hash, SplFixedArray $list, mixed $key, mixed $value): SplFixedArray
    {
        $len = $list->getSize();

        for ($i = 0; $i < $len; ++$i) {
            $child = $list[$i];
            if ($child->key === $key) {
                return $value === $child->value
                    ? $list
                    : SplFixedArrayOps::arrayUpdate($i, new LeafNode($hash, $key, $value), $list);
            }
        }

        return SplFixedArrayOps::arraySpliceIn($len, new LeafNode($hash, $key, $value), $list);
    }
}