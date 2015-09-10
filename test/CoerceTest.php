<?php

namespace ThriftMapperTest;

use ThriftMapper\ThriftMapper;
use ThriftTest\Bonk;
use ThriftTest\NestedListsBonk;
use ThriftTest\Insanity;
use ThriftTest\Xtruct;

class CoerceTest extends \PHPUnit_Framework_TestCase
{
    public function testBonk()
    {
        $bonk = new Bonk([
            'message' => 123,
            'type' => 'Hello!',
        ]);
        $bonk = ThriftMapper::coerce($bonk);

        $expected = new Bonk([
            'message' => '123',
            'type' => 0,
        ]);
        $this->assertSame($expected->message, $bonk->message);
        $this->assertSame($expected->type, $bonk->type);
    }

    public function testInsanity()
    {
        $insanity = new Insanity([
            'userMap' => [1 => 10, 2 => '20'],
            'xtructs' => [
                new Xtruct([ "string_thing" => 1, "byte_thing" => '2', "i32_thing" => 3.3, "i64_thing" => true ])
            ],
        ]);
        $insanity = ThriftMapper::coerce($insanity);

        $expected = new Insanity([
            'userMap' => [1 => 10, 2 => 20],
            'xtructs' => [
                new Xtruct([ "string_thing" => '1', "byte_thing" => 2, "i32_thing" => 3, "i64_thing" => 1 ])
            ],
        ]);

        $this->assertSame($expected->userMap[1], $insanity->userMap[1]);
        $this->assertSame($expected->userMap[2], $insanity->userMap[2]);
        $this->assertSame($expected->xtructs[0]->string_thing, $insanity->xtructs[0]->string_thing);
        $this->assertSame($expected->xtructs[0]->byte_thing, $insanity->xtructs[0]->byte_thing);
        $this->assertSame($expected->xtructs[0]->i32_thing, $insanity->xtructs[0]->i32_thing);
        $this->assertSame($expected->xtructs[0]->i64_thing, $insanity->xtructs[0]->i64_thing);
    }

    public function testNestedListBonk()
    {
        $nestedListBonk = new NestedListsBonk([
            'bonk' => [
                [
                    [
                        new Bonk(['message' => true, 'type' => true]),
                        new Bonk(['message' => false, 'type' => false]),
                    ],
                ],
                [
                    [
                        new Bonk(['message' => null]),
                        new Bonk(['message' => 2.2, 'type' => 2.2]),
                    ],
                ],
            ],
        ]);
        $nestedListBonk = ThriftMapper::coerce($nestedListBonk);

        $expected = new NestedListsBonk([
            'bonk' => [
                [
                    [
                        new Bonk(['message' => '1', 'type' => 1]),
                        new Bonk(['message' => '', 'type' => 0]),
                    ],
                ],
                [
                    [
                        new Bonk(['message' => null, 'type' => null]),
                        new Bonk(['message' => '2.2', 'type' => 2]),
                    ],
                ],
            ],
        ]);

        $this->assertSame($expected->bonk[0][0][0]->message, $nestedListBonk->bonk[0][0][0]->message);
        $this->assertSame($expected->bonk[0][0][0]->type, $nestedListBonk->bonk[0][0][0]->type);
        $this->assertSame($expected->bonk[0][0][1]->message, $nestedListBonk->bonk[0][0][1]->message);
        $this->assertSame($expected->bonk[0][0][1]->type, $nestedListBonk->bonk[0][0][1]->type);

        $this->assertSame($expected->bonk[1][0][0]->message, $nestedListBonk->bonk[1][0][0]->message);
        $this->assertSame($expected->bonk[1][0][0]->type, $nestedListBonk->bonk[1][0][0]->type);
        $this->assertSame($expected->bonk[1][0][1]->message, $nestedListBonk->bonk[1][0][1]->message);
        $this->assertSame($expected->bonk[1][0][1]->type, $nestedListBonk->bonk[1][0][1]->type);
    }

    public function testBadType()
    {
        $this->setExpectedException('\ThriftMapper\CoerceException');
        $bonk = new Bonk([
            'message' => [],
        ]);
        $bonk = ThriftMapper::coerce($bonk);
    }

    public function testBadType2()
    {
        $this->setExpectedException('\ThriftMapper\CoerceException');
        $nestedListBonk = new NestedListsBonk([
            'bonk' => [
                [
                    null
                ],
            ],
        ]);
        $nestedListBonk = ThriftMapper::coerce($nestedListBonk);
    }

    public function testBadType3()
    {
        $this->setExpectedException('\ThriftMapper\CoerceException');
        $insanity = new Insanity([
            'userMap' => [1 => null],
        ]);
        $insanity = ThriftMapper::coerce($insanity);
    }
}
