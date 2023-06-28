<?php

declare(strict_types=1);

namespace Tests\Hamt;

use Fp\DataStructures\Hamt\LeafNode;
use PHPUnit\Framework\TestCase;

final class OpsTest extends TestCase
{
    public function testGet(): void
    {
        $map = (new LeafNode(1, 1, '1'))
            ->updated(0, 2, 2, '2')
            ->updated(0, 3, 3, '3')
            ->updated(0, 4, 4, '4')
            ->updated(0, 5, 5, '5')
            ->updated(0, 50, 50, '50')
            ->updated(0, 50, 51, '51');

        $this->assertEquals('2', $map->get(2, 2));
        $this->assertEquals('5', $map->get(5, 5));
        $this->assertNull($map->get(6, 5));
    }

    public function testRemoved(): void
    {
        $map = (new LeafNode(1, 1, '1'))
            ->updated(0, 5, 5, '5')
            ->updated(0, 50, 50, '50')
            ->updated(0, 50, 51, '51')
            ->removed(0, 5, 5);

        $expected = [1 => '1', 51 => '51', 50 => '50'];

        $this->assertEquals($expected, iterator_to_array($map));
    }

    public function testIterator(): void
    {
        $map = new LeafNode(1, 1, '1');
        $map = $map->updated(0, 2, 2, '2');
        $map = $map->updated(0, 3, 3, '3');
        $map = $map->updated(0, 4, 4, '4');
        $map = $map->updated(0, 5, 5, '5');
        $map = $map->updated(0, 50, 50, '50');
        $map = $map->updated(0, 50, 51, '51');

        $expected = [
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
            51 => '51',
            50 => '50',
        ];

        $this->assertEquals($expected, iterator_to_array($map));
    }

}
