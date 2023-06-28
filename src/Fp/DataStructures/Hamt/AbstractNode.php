<?php

namespace Fp\DataStructures\Hamt;

use Fp\DataStructures\BitOps;
use Generator;
use IteratorAggregate;
use Traversable;

/**
 * @template TKey
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
abstract class AbstractNode implements IteratorAggregate {
    /**
     * 0 = @see LeafNode::$tag
     * 1 = @see BitmapNode::$tag
     * 2 = @see ArrayNode::$tag
     * 3 = @see CollisionNode::$tag
     */
    protected int $tag;

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @param TValue $value
     * @return AbstractNode<TKey, TValue>
     */
    abstract public function updated(int $shift, int $hash, mixed $key, mixed $value): AbstractNode;

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @return AbstractNode<TKey, TValue>|null
     */
    abstract public function removed(int $shift, int $hash, mixed $key): ?AbstractNode;

    public function getIterator(): Traversable
    {
        return $this->iterate($this);
    }

    /**
     * @param AbstractNode $node
     * @return Generator<TKey, TValue>
     */
    private function iterate(AbstractNode $node): Generator
    {
        switch ($node->tag) {
            case 0:
                /** @var LeafNode $node */
                yield $node->key => $node->value;
                break;
            case 1:
            case 2:
            case 3:
                /** @var BitmapNode|ArrayNode|CollisionNode $node */
                foreach ($node->children as $child) {
                    yield from $this->iterate($child);
                }
                break;
        }
    }

    /**
     * @param int $hash
     * @param TKey $key
     * @return TValue|null
     */
    public function get(mixed $key, int $hash): mixed
    {
        $node = $this;
        $shift = 0;

        while (true) {
            switch ($node->tag) {
                case 0:
                    /** @var LeafNode $node */
                    return $key === $node->key
                        ? $node->value
                        : null;
                case 1:
                    /** @var BitmapNode $node */
                    $bit = 1 << BitOps::hashFragment($shift, $hash);
                    if ($node->bitmap & $bit) {
                        $node = $node->children[BitOps::indexFromBitmap($node->bitmap, $bit)];
                        $shift += 5;
                        break;
                    }
                    return null;
                case 2:
                    /** @var ArrayNode $node */
                    $node = $node->children[BitOps::hashFragment($shift, $hash)];
                    if ($node) {
                        $shift += 5;
                        break;
                    }
                    return null;
                case 3:
                    /** @var CollisionNode $node */
                    if ($hash === $node->hash) {
                        foreach ($node->children as $child) {
                            if ($key === $child->key) {
                                return $child->value;
                            }
                        }
                    }
                    return null;
            }
        }
    }
}