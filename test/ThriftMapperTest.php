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
}
