<?php

namespace Fp\DataStructures\Hamt;

use Fp\DataStructures\BitOps;
use Fp\DataStructures\SplFixedArrayOps;
use SplFixedArray;

/**
 * @template TKey
 * @template TValue
 * @extends AbstractNode<TKey, TValue>
 */
abstract class LeafLikeNode extends AbstractNode
{
    public function mergeLeaf(int $shift, LeafNode $that): AbstractNode
    {
        if ($this->hash == $that->hash) {
            // collision
            if ($this instanceof CollisionNode) {
                $children = SplFixedArrayOps::arrayPrepend($that, $this->children);
            } else {
                $children = new SplFixedArray(2);
                $children[0] = $that;
                $children[1] = $this;
            }
            return new CollisionNode($this->hash, $children);
        }

        // no collision
        $thisFrag = BitOps::hashFragment($shift, $this->hash);
        $thatFrag = BitOps::hashFragment($shift, $that->hash);

        if ($thisFrag == $thatFrag) {
            // same hash fragments
            // unable to place nodes in the same bitmap
            // shift down to the next level
            $children = new SplFixedArray(1);
            $children[0] = $this->mergeLeaf($shift + 5, $that);;
        } else {
            // different hash fragments
            // able to place nodes in the same bitmap
            $children = new SplFixedArray(2);
            $children[0] = $thisFrag < $thatFrag ? $this : $that;
            $children[1] = $thisFrag < $thatFrag ? $that : $this;
        }

        return new BitmapNode((1 << $thisFrag) | (1 << $thatFrag), $children);
    }
}