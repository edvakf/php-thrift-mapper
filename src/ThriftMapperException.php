<?php

namespace ThriftMapper;

class ThriftMapperException extends \Exception
{
    public function __construct($msg)
    {
        parent::__construct($msg);
    }
}
