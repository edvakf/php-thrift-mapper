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
            } else if (isset($spec['key']) && isset($spec['key']['class'])) {
                throw new \ThriftMapperException("Map key cannot be a struct: " . $key);
            } else if (isset($spec['val']) && isset($spec['val']['class'])) {
                if (!is_array($phpArray[$key])) {
                    throw new \ThriftMapperException("Field must be an associative array: " . $key);
                }
                $map = [];
                foreach ($phpArray[$key] as $k => $v) {
                    $map[$k] = self::map(new $spec['val']['class'], $v);
                }
                $thriftStruct->{$key} = $map;
            } else if (isset($spec['elem']) && isset($spec['elem']['class'])) {
                if (!is_array($phpArray[$key])) {
                    throw new \ThriftMapperException("Field must be an array: " . $key);
                }
                $lst = [];
                foreach ($phpArray[$key] as $v) {
                    $lst[] = self::map(new $spec['elem']['class'], $v);
                }
                $thriftStruct->{$key} = $lst;
            } else {
                $thriftStruct->{$key} = $phpArray[$key];
            }
        }
        return $thriftStruct;
    }
}
