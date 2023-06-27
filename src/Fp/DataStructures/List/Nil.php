<?php

declare(strict_types=1);

namespace Fp\DataStructures\List;

/**
 * @extends LinkedList<empty>
 */
final class Nil extends LinkedList
{
    private static ?Nil $instance = null;

    private function __construct() {
        self::$instance = $this;
    }

    public static function getInstance(): Nil
    {
        return self::$instance ?? new Nil();
    }

    public function head(): mixed
    {
        return null;
    }

    public function tail(): LinkedList
    {
        return $this;
    }

    public function isEmpty(): bool
    {
        return true;
    }
}
