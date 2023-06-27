<?php

declare(strict_types=1);

namespace Fp\DataStructures\List;

use Iterator;

/**
 * @internal
 * @template TV
 * @implements Iterator<int, TV>
 */
final class ListIterator implements Iterator
{
    private AbstractNode $originalList;
    private AbstractNode $list;
    private int $idx;

    /**
     * @param AbstractNode<TV> $list
     */
    public function __construct(AbstractNode $list)
    {
        $this->originalList = $this->list = $list;
        $this->idx = 0;
    }

    public function current(): mixed
    {
        return $this->list instanceof ConsNode
            ? $this->list->head
            : null;
    }

    public function next(): void
    {
        if ($this->list instanceof ConsNode) {
            $this->list = $this->list->tail;
            $this->idx++;
        }
    }

    public function key(): int
    {
        return $this->idx;
    }

    public function valid(): bool
    {
        return $this->list instanceof ConsNode;
    }

    public function rewind(): void
    {
        $this->list = $this->originalList;
        $this->idx = 0;
    }
}
