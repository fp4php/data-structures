<?php

namespace Fp\DataStructures\Hamt;

/**
 * @template TKey
 * @template TValue
 * @extends LeafLikeNode<TKey, TValue>
 */
final class LeafNode extends LeafLikeNode {

    protected int $tag = 0;

    /**
     * @param int $hash
     * @param TKey $key
     * @param TValue $value
     */
    public function __construct(
        public readonly int $hash,
        public readonly mixed $key,
        public readonly mixed $value
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
        if ($key === $this->key) {
            return
                $value === $this->value
                    ? $this // same entries
                    : new LeafNode($hash, $key, $value); // same keys, different values
        } else {
            // different keys
            // merge leaf nodes into bitmap or collision node
            return $this->mergeLeaf($shift, new LeafNode($hash, $key, $value));
        }
    }

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @return AbstractNode<TKey, TValue>|null
     */
    public function removed(int $shift, int $hash, mixed $key): ?AbstractNode
    {
        return $this->key == $key ? null : $this;
    }
}