<?php

namespace ThriftMapper;

use Thrift\Type\TType;

class ThriftMapper
{
    public static function map($thriftStruct, $phpArray)
    {
        foreach ($thriftStruct::$_TSPEC as $index => $spec) {
            $key = $spec['var'];
            if (!isset($phpArray[$key])) {
                continue;
            }

            $thriftStruct->{$key} = self::map_($spec, $phpArray[$key]);
        }
        return $thriftStruct;
    }

    private static function map_($spec, $phpVal)
    {
        $type = $spec['type'];

        if ($type === TType::STRUCT) {

            return self::map(new $spec['class'], $phpVal);

        } else if ($type === TType::LST || $type === TType::SET) {

            if (!is_array($phpVal)) {
                throw new \ThriftMapperException("Field must be an array");
            }
            $lst = [];
            foreach ($phpVal as $v) {
                $lst[] = self::map_($spec['elem'], $v);
            }
            return $lst;

        } else if ($type === TType::MAP) {

            if (!is_array($phpVal)) {
                throw new \ThriftMapperException("Field must be an associative array");
            }
            $map = [];
            foreach ($phpVal as $k => $v) {
                $map[self::map_($spec['key'], $k)] = self::map_($spec['val'], $v);
            }
            return $map;

        } else {

            return $phpVal;

        }
    }
}
