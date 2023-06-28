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

    protected int $tag = 3;

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
     * @return AbstractNode<TKey, TValue>|null
     */
    public function removed(int $shift, int $hash, mixed $key): ?AbstractNode
    {
        foreach ($this->children as $idx => $child) {
            if ($child->key === $key) {
                // entry is found by key
                // remove this entry from collision list
                $children = SplFixedArrayOps::arraySpliceOut($idx, $this->children);
                return $this->collapseSingle($hash, $children);
            }
        }

        return $this;
    }

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @param TValue $value
     * @return AbstractNode<TKey, TValue>
     */
    public function updated(int $shift, int $hash, mixed $key, mixed $value): AbstractNode
    {
        if ($hash !== $this->hash) {
            // different hashes
            // merge collision node with leaf node into bitmap node
            return $this->mergeLeaf($shift, new LeafNode($hash, $key, $value));
        }

        // same hashes = collision

        foreach ($this->children as $idx => $child) {
            if ($child->key === $key) {
                if ($value === $child->value) {
                    // entry already present in collision list
                    // no modifications required
                    return $this;
                }

                // same entry keys, different entry values
                // modify entry value
                $children = SplFixedArrayOps::arrayUpdate($idx, new LeafNode($hash, $key, $value), $this->children);
                return $this->collapseSingle($hash, $children);
            }
        }

        // no entries with given key
        // add entry to collision list
        $children = SplFixedArrayOps::arrayAppend(new LeafNode($hash, $key, $value), $this->children);
        return $this->collapseSingle($hash, $children);
    }

    /**
     * @param int $hash
     * @param SplFixedArray<LeafNode<TKey, TValue>> $children
     * @return AbstractNode<TKey, TValue>
     */
    private function collapseSingle(int $hash, SplFixedArray $children): AbstractNode
    {
        return $children->getSize() > 1
            ? new CollisionNode($hash, $children)
            : $children[0]; // collapse single element collision list
    }

}