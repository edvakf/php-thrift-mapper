<?php

namespace ThriftMapper;

class ThriftMapper
{
    public static function map($thriftStruct, $phpArray)
    {
        foreach ($thriftStruct::$_TSPEC as $index => $spec) {
            $key = $spec['var'];
            if (!isset($phpArray[$key])) {
                continue;
            }
            if (isset($spec['class'])) {
                $thriftStruct->{$key} = self::map(new $spec['class'], $phpArray[$key]);
            } else {
                $thriftStruct->{$key} = $phpArray[$key];
            }
        }
        return $thriftStruct;
    }
}
