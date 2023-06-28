<?php

declare(strict_types=1);

namespace Fp\DataStructures\List;

use Iterator;
use IteratorAggregate;

/**
 * @template-covariant TValue
 * @implements IteratorAggregate<int, TValue>
 */
abstract class AbstractNode implements IteratorAggregate
{
    /**
     * @template TValueIn
     *
     * @param TValueIn $elem
     * @return AbstractNode<TValue|TValueIn>
     */
    public function prepended(mixed $elem): AbstractNode
    {
        return new ConsNode($elem, $this);
    }

    /**
     * @return Iterator<int, TValue>
     */
    public function getIterator(): Iterator
    {
        return new ListIterator($this);
    }

    abstract public function isEmpty(): bool;

    /**
     * @return TValue|null
     */
    abstract public function head(): mixed;

    /**
     * @return AbstractNode<TValue>
     */
    abstract public function tail(): AbstractNode;
}
