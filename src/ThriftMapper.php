<?php

namespace ThriftMapper;

use Thrift\Type\TType;

class ThriftMapper
{
    public static function map($thriftStruct, $phpArray, $path = null)
    {
        foreach ($thriftStruct::$_TSPEC as $index => $spec) {
            $key = $spec['var'];
            if (!isset($phpArray[$key])) {
                continue;
            }

            $path = is_null($path) ? $key : $path . $key;
            $thriftStruct->{$key} = self::map_($spec, $phpArray[$key], $path);
        }
        return $thriftStruct;
    }

    private static function map_($spec, $phpVal, $path)
    {
        $type = $spec['type'];

        if ($type === TType::STRUCT) {

            return self::map(new $spec['class'], $phpVal, $path);

        } else if ($type === TType::LST || $type === TType::SET) {

            if (!is_array($phpVal)) {
                throw new ThriftMapperException("Value must be an array: " . $path);
            }
            $lst = [];
            foreach ($phpVal as $i => $v) {
                $lst[] = self::map_($spec['elem'], $v, $path . '[' . $i . ']');
            }
            return $lst;

        } else if ($type === TType::MAP) {

            if (!is_array($phpVal)) {
                throw new ThriftMapperException("Value must be an associative array: " . $path);
            }
            $map = [];
            foreach ($phpVal as $k => $v) {
                $map[self::map_($spec['key'], $k, $path . '[' . $k . ']')] =
                    self::map_($spec['val'], $v, $path . '[' . $k . ']');
            }
            return $map;

        } else {

            return $phpVal;

        }
    }
}
