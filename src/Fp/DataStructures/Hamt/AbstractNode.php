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
     * @var 'LEAF'|'COLLISION'|'BITMAP'|'ARRAY'
     */
    protected string $tag;

    /**
     * @param int $shift
     * @param int $hash
     * @param TKey $key
     * @param TValue $value
     * @return AbstractNode<TKey, TValue>
     */
    abstract public function updated(int $shift, int $hash, mixed $key, mixed $value): AbstractNode;

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
            case 'LEAF':
                /** @var LeafNode $node */
                yield $node->key => $node->value;
                break;
            case 'BITMAP':
            case 'ARRAY':
            case 'COLLISION':
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
                case 'LEAF':
                    /** @var LeafNode $node */
                    return $key === $node->key
                        ? $node->value
                        : null;
                case 'COLLISION':
                    /** @var CollisionNode $node */
                    if ($hash === $node->hash) {
                        foreach ($node->children as $child) {
                            if ($key === $child->key) {
                                return $child->value;
                            }
                        }
                    }
                    return null;
                case 'BITMAP':
                    /** @var BitmapNode $node */
                    $bit = 1 << BitOps::hashFragment($shift, $hash);
                    if ($node->bitmap & $bit) {
                        $node = $node->children[BitOps::indexFromBitmap($node->bitmap, $bit)];
                        $shift += 5;
                        break;
                    }
                    return null;
                case 'ARRAY':
                    /** @var ArrayNode $node */
                    $node = $node->children[BitOps::hashFragment($shift, $hash)];
                    if ($node) {
                        $shift += 5;
                        break;
                    }
                    return null;
            }
        }
    }
}