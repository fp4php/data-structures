<?php

namespace Fp\DataStructures\Hamt;

/**
 * @template TKey
 * @template TValue
 * @extends LeafLikeNode<TKey, TValue>
 */
final class LeafNode extends LeafLikeNode {

    protected string $tag = 'LEAF';

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
            return $value === $this->value ? $this : new LeafNode($hash, $key, $value);
        } else {
            return $this->mergeLeaf($shift, new LeafNode($hash, $key, $value));
        }
    }
}