<?php

namespace Fp\DataStructures\Hamt;

use Fp\DataStructures\BitOps;
use Fp\DataStructures\SplFixedArrayOps;
use SplFixedArray;

/**
 * @template TKey
 * @template TValue
 * @extends HashArrayMappedTrie<TKey, TValue>
 */
abstract class LeafLikeNode extends HashArrayMappedTrie {
    public function merge(int $shift, LeafNode $rhs): HashArrayMappedTrie
    {
        $lhs = $this;

        if ($lhs->hash == $rhs->hash) {
            if ($lhs instanceof CollisionNode) {
                $children = SplFixedArrayOps::arraySpliceIn($lhs->children->getSize(), $rhs, $lhs->children);
            } else {
                $children = new SplFixedArray(2);
                $children[0] = $rhs;
                $children[1] = $lhs;
            }
            return new CollisionNode($this->hash, $children);
        }

        $lhsFrag = BitOps::hashFragment($shift, $lhs->hash);
        $rhsFrag = BitOps::hashFragment($shift, $rhs->hash);

        if ($lhsFrag == $rhsFrag) {
            $children = new SplFixedArray(1);
            $children[0] = $lhs->merge($shift + 5, $rhs);;
        } else {
            $children = new SplFixedArray(2);
            $children[0] = $lhsFrag < $rhsFrag ? $lhs : $rhs;
            $children[1] = $lhsFrag < $rhsFrag ? $rhs : $lhs;
        }

        return new BitmapNode((1 << $lhsFrag) | (1 << $rhsFrag), $children);
    }
}