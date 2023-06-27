<?php

declare(strict_types=1);

namespace Fp\DataStructures\List;

/**
 * @template-covariant TValue
 * @extends AbstractNode<TValue>
 */
final class ConsNode extends AbstractNode
{
    /**
     * @param TValue $head
     * @param AbstractNode<TValue> $tail
     */
    public function __construct(
        public mixed        $head,
        public AbstractNode $tail
    ) { }

    public function head(): mixed
    {
        return $this->head;
    }

    public function tail(): AbstractNode
    {
        return $this->tail;
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
