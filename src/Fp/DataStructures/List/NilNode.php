<?php

declare(strict_types=1);

namespace Fp\DataStructures\List;

/**
 * @extends AbstractNode<empty>
 */
final class NilNode extends AbstractNode
{
    private static ?NilNode $instance = null;

    private function __construct() {
        self::$instance = $this;
    }

    public static function getInstance(): NilNode
    {
        return self::$instance ?? new NilNode();
    }

    public function head(): mixed
    {
        return null;
    }

    public function tail(): AbstractNode
    {
        return $this;
    }

    public function isEmpty(): bool
    {
        return true;
    }
}
