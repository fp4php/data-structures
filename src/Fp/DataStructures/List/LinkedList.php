<?php

declare(strict_types=1);

namespace Fp\DataStructures\List;

use Iterator;
use IteratorAggregate;

/**
 * @template-covariant TValue
 */
abstract class LinkedList implements IteratorAggregate
{
    /**
     * @template TValueIn
     *
     * @param TValueIn $elem
     * @return LinkedList<TValue|TValueIn>
     */
    public function prepended(mixed $elem): LinkedList
    {
        return new Cons($elem, $this);
    }

    /**
     * @return Iterator<int, TValue>
     */
    public function getIterator(): Iterator
    {
        return new LinkedListIterator($this);
    }

    abstract public function isEmpty(): bool;

    /**
     * @return TValue|null
     */
    abstract public function head(): mixed;

    /**
     * @return LinkedList<TValue>
     */
    abstract public function tail(): LinkedList;
}
