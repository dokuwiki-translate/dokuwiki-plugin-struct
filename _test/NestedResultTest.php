<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\NestedResult;
use dokuwiki\plugin\struct\meta\Value;
use dokuwiki\plugin\struct\types\Text;

/**
 * Tests for the NestedResult class
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class NestedResultTest extends StructTest
{
    protected $simpleItems = [
        ['car', 'audi', 'a80'],
        ['car', 'audi', 'a4'],
        ['car', 'audi', 'quattro'],
        ['car', 'bmw', 'i3'],
        ['car', 'bmw', 'mini'],
        ['car', 'bmw', 'z1'],
        ['laptop', 'apple', 'pro 16'],
        ['laptop', 'apple', 'air'],
        ['laptop', 'apple', 'm1'],
        ['laptop', 'dell', 'xps'],
        ['laptop', 'dell', 'inspiron'],
        ['laptop', 'dell', 'latitude'],
    ];

    protected $multiItems = [
        [['green', 'yellow'], 'car', 'audi', 'a80'],
        [['yellow', 'blue'], 'car', 'audi', 'a4'],
        [['black', 'green'], 'car', 'audi', 'quattro'],
        [['red', 'black'], 'car', 'bmw', 'i3'],
        [['blue', 'gray'], 'car', 'bmw', 'mini'],
        [['red', 'black'], 'car', 'bmw', 'z1'],
        [['green', 'blue'], 'laptop', 'apple', 'pro 16'],
        [['red', 'blue'], 'laptop', 'apple', 'air'],
        [['black', 'red'], 'laptop', 'apple', 'm1'],
        [['gray', 'green'], 'laptop', 'dell', 'xps'],
        [['blue', 'yellow'], 'laptop', 'dell', 'inspiron'],
        [['gray', 'yellow'], 'laptop', 'dell', 'latitude'],
    ];

    protected $multiHoleItems = [
        [['green', 'yellow'], 'car', 'audi', 'a80'],
        [[], 'car', 'audi', 'a4'],
        [['black', 'green'], '', 'audi', 'quattro'],
        [['red', 'black'], 'car', 'bmw', 'i3'],
        [['blue', 'gray'], 'car', 'bmw', 'mini'],
        [['red', 'black'], 'car', 'bmw', 'z1'],
        [['green', 'blue'], 'laptop', 'apple', 'pro 16'],
        [['red', 'blue'], 'laptop', 'apple', 'air'],
        [['black', 'red'], 'laptop', 'apple', 'm1'],
        [[], 'laptop', 'dell', 'xps'],
        [['blue', 'yellow'], '', 'dell', 'inspiron'],
        [['gray', 'yellow'], 'laptop', 'dell', 'latitude'],
    ];

    protected $multiMultiItems = [
        [['metal', 'wood'], ['green', 'yellow'], 'car', 'audi', 'a80'],
        [['metal', 'wood', 'plastic'], ['yellow', 'blue'], 'car', 'audi', 'a4'],
        [['plastic', 'metal'], ['red', 'blue'], 'laptop', 'apple', 'pro 16'],
        [['metal', 'plastic'], ['black', 'red'], 'laptop', 'apple', 'air'],
    ];


    /**
     * Create a result set from a given flat array
     * @param array $rows
     * @return array
     */
    protected function makeResult($rows)
    {
        $result = [];

        foreach ($rows as $row) {
            $resultRow = [];
            foreach ($row as $cell) {
                $resultRow[] = new Value(
                    new Column(
                        10,
                        new Text(null, '', is_array($cell)),
                        0,
                        true,
                        'test'
                    ),
                    $cell
                );
            }
            $result[] = $resultRow;
        }

        return $result;
    }

    /**
     * Don't nest at all
     */
    public function testSimpleZeroLevel()
    {
        $result = $this->makeResult($this->simpleItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(0);

        $this->assertCount(0, $root->getChildren(), 'no children expected');
        $this->assertCount(12, $root->getResultRows(), '12 result rows expected');
    }


    /**
     * Nest by the first level, no multi values
     */
    public function testSimpleOneLevel()
    {
        $result = $this->makeResult($this->simpleItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(1);
        $tree = $root->getChildren();

        $this->assertCount(2, $tree, '2 root nodes expected');
        $this->assertEquals('car', $tree[0]->getValueObject()->getValue());
        $this->assertEquals('laptop', $tree[1]->getValueObject()->getValue());

        $this->assertCount(0, $tree[0]->getChildren(), 'no children expected');
        $this->assertCount(0, $tree[1]->getChildren(), 'no children expected');

        $this->assertCount(6, $tree[0]->getResultRows(), 'result rows');
        $this->assertCount(6, $tree[1]->getResultRows(), 'result rows');

        $this->assertEquals('a80', $tree[0]->getResultRows()[0][1]->getValue(), 'Audi 80 expected');
        $this->assertEquals('pro 16', $tree[1]->getResultRows()[0][1]->getValue(), 'Mac Pro 16 expected');
    }


    /**
     * Nest by two levels, no multi values
     */
    public function testSimpleTwoLevels()
    {
        $result = $this->makeResult($this->simpleItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(2);
        $tree = $root->getChildren();

        $this->assertCount(2, $tree, '2 root nodes expected');
        $this->assertEquals('car', $tree[0]->getValueObject()->getValue());
        $this->assertEquals('laptop', $tree[1]->getValueObject()->getValue());

        $this->assertCount(2, $tree[0]->getChildren(), '2 second level nodes expected');
        $this->assertCount(2, $tree[1]->getChildren(), '2 second level nodes expected');

        $this->assertCount(3, $tree[0]->getChildren()[0]->getResultRows(), 'result rows');
        $this->assertCount(3, $tree[0]->getChildren()[1]->getResultRows(), 'result rows');
        $this->assertCount(3, $tree[1]->getChildren()[0]->getResultRows(), 'result rows');
        $this->assertCount(3, $tree[1]->getChildren()[1]->getResultRows(), 'result rows');


        $this->assertEquals('a80', $tree[0]->getChildren()[0]->getResultRows()[0][0]->getValue(), 'Audi 80 expected');
        $this->assertEquals('pro 16', $tree[1]->getChildren()[0]->getResultRows()[0][0]->getValue(), 'Mac Pro 16 expected');
    }

    /**
     * Nest by three levels, the first one being multi-value
     */
    public function testMultiThreeLevels()
    {
        $result = $this->makeResult($this->multiItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(3);
        $tree = $root->getChildren(); // nest: color, type, brand -> model

        $this->assertCount(6, $tree, '6 root nodes of colors expected');

        // Values on the first level will be multi-values, thus returning arrays
        $this->assertEquals('black', $tree[0]->getValueObject()->getValue()[0]);
        $this->assertEquals('blue', $tree[1]->getValueObject()->getValue()[0]);
        $this->assertEquals('gray', $tree[2]->getValueObject()->getValue()[0]);
        $this->assertEquals('green', $tree[3]->getValueObject()->getValue()[0]);
        $this->assertEquals('red', $tree[4]->getValueObject()->getValue()[0]);
        $this->assertEquals('yellow', $tree[5]->getValueObject()->getValue()[0]);

        // Results should now show up under multiple top-level nodes
        $this->assertEquals('a80',
            $tree[3] // green
            ->getChildren()[0] // car
            ->getChildren()[0] // audi
            ->getResultRows()[0][0] // a80
            ->getValue(),
            'green car audi a80 expected'
        );
        $this->assertEquals('a80',
            $tree[5] // yellow
            ->getChildren()[0] // car
            ->getChildren()[0] // audi
            ->getResultRows()[0][0] // a80
            ->getValue(),
            'yellow car audi a80 expected'
        );
    }

    public function testMultiHoles()
    {
        $result = $this->makeResult($this->multiHoleItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(3);
        $tree = $root->getChildren(); // nest: color, type, brand -> model
        $this->assertCount(7, $tree, '6 root nodes of colors + 1 n/a expected');  // should have one n/a node
        $this->assertCount(2, $tree[6]->getChildren(), 'top n/a node should have car, laptop');
        $this->assertCount(3, $tree[0]->getChildren(), 'black should have car,laptop,n/a');
    }

    /**
     * Nest by two multi value levels
     */
    public function testMultiMultiTwoLevels()
    {
        $result = $this->makeResult($this->multiMultiItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(2);
        $tree = $root->getChildren(); // nest: material, color, *

        $this->assertCount(3, $tree, '3 root nodes of material expected');
        $this->assertCount(1, $tree[0]->getChildren()[0]->getResultRows(), '1 metal black row expected');
    }

    /**
     * Nest by two multi value levels with indexing
     */
    public function testMultiMultiTwoLevelsIndex()
    {
        $result = $this->makeResult($this->multiMultiItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(2, 1);
        $tree = $root->getChildren(); // nest: index, material, color, *

        $this->assertCount(3, $tree, '3 root index nodes  expected');
        $this->assertEquals('M', $tree[0]->getValueObject()->getValue(), 'M expected');
        $this->assertCount(1, $tree[0]->getChildren(), '1 metal sub node under M expected');
    }

    /**
     * Index a flat result with no multi values
     */
    public function testSimpleIndex()
    {
        $result = $this->makeResult($this->simpleItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(0, 2);
        $tree = $root->getChildren();

        $this->assertCount(2, $tree, '2 root index nodes  expected');
        $this->assertEquals('CA', $tree[0]->getValueObject()->getValue(), 'CA(r) index expected');
        $this->assertEquals('LA', $tree[1]->getValueObject()->getValue(), 'LA(ptop) index expected');

        $this->assertCount(6, $tree[0]->getResultRows(), '6 rows under CA expected');
    }


    /**
     * Index a flat result with multi values
     */
    public function testMultiIndex()
    {
        $result = $this->makeResult($this->multiItems);
        $nestedResult = new NestedResult($result);
        $root = $nestedResult->getRoot(0, 2);
        $tree = $root->getChildren();

        $this->assertCount(4, $tree, '4 root index nodes  expected');
        $this->assertEquals('BL', $tree[0]->getValueObject()->getValue(), 'BL(ack|blue) index expected');

        $this->assertCount(4, $tree[0]->getResultRows(), '4 rows under BL expected');
    }
}
