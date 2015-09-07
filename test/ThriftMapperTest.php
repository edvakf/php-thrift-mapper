<?php

namespace ThriftMapperTest;

use ThriftMapper\ThriftMapper;
use ThriftTest\Bonk;
use ThriftTest\Insanity;

class ThriftMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testBonk()
    {
        $ary = [
            'message' => 'Hello!',
            'type' => 123,
        ];
        $bonk = ThriftMapper::map(new Bonk(), $ary);

        $this->assertSame($ary['message'], $bonk->message);
        $this->assertSame($ary['type'], $bonk->type);
    }

    public function testInsanity()
    {
        $ary = [
            'userMap' => [1 => 10, 2 => 20],
            'xtructs' => [ [ "string_thing" => "1", "byte_thing" => 2, "i32_thing" => 3, "i64_thing" => 4 ] ],
        ];
        $insanity = ThriftMapper::map(new Insanity(), $ary);

        $this->assertSame($ary['userMap'][1], $insanity->userMap[1]);
        $this->assertSame($ary['userMap'][2], $insanity->userMap[2]);
        $this->assertSame($ary['xtructs'][0]['string_thing'], $insanity->xtructs[0]->string_thing);
        $this->assertSame($ary['xtructs'][0]['byte_thing'], $insanity->xtructs[0]->byte_thing);
        $this->assertSame($ary['xtructs'][0]['i32_thing'], $insanity->xtructs[0]->i32_thing);
        $this->assertSame($ary['xtructs'][0]['i64_thing'], $insanity->xtructs[0]->i64_thing);
    }
}
