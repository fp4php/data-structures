<?php

declare(strict_types=1);

namespace Fp\DataStructures\List;

/**
 * @template-covariant TValue
 * @extends LinkedList<TValue>
 */
final class Cons extends LinkedList
{
    /**
     * @param TValue $head
     * @param LinkedList<TValue> $tail
     */
    public function __construct(
        public mixed $head,
        public LinkedList $tail
    ) { }

    public function head(): mixed
    {
        return $this->head;
    }

    public function tail(): LinkedList
    {
        return $this->tail;
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
