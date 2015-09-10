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
        switch ($spec['type']) {
        case TType::STRUCT:
            return self::map(new $spec['class'], $phpVal, $path);
        case TType::LST:
        case TType::SET:
            if (!is_array($phpVal)) {
                throw new MapException("Value must be an array: " . $path);
            }
            $lst = [];
            foreach ($phpVal as $i => $v) {
                $lst[] = self::map_($spec['elem'], $v, $path . '[' . $i . ']');
            }
            return $lst;
        case TType::MAP:
            if (!is_array($phpVal)) {
                throw new MapException("Value must be an associative array: " . $path);
            }
            $map = [];
            foreach ($phpVal as $k => $v) {
                $map[self::map_($spec['key'], $k, $path . '[' . $k . ']')] =
                    self::map_($spec['val'], $v, $path . '[' . $k . ']');
            }
            return $map;
        default:
            return $phpVal;
        }
    }

    public static function coerce($thriftStruct, $path = null)
    {
        // $_TSPEC does not have optionality informatioin, so allow null here.
        // However, `thrift -gen php:validator,json` generates a validation step
        // to make a null at a non-optional field an error.
        if (is_null($thriftStruct)) {
            return null;
        }

        foreach ($thriftStruct::$_TSPEC as $index => $spec) {
            $key = $spec['var'];
            $path = is_null($path) ? $key : $path . $key;
            $thriftStruct->{$key} = self::coerce_($spec, $thriftStruct->{$key}, $path);
        }
        return $thriftStruct;
    }

    private static function coerce_($spec, $thriftVal, $path)
    {
        if (is_null($thriftVal)) {
            return null;
        }

        switch ($spec['type']) {
        case TType::STRUCT:
            return self::coerce($thriftVal);
        case TType::LST:
        case TType::SET:
            if (!is_array($thriftVal)) {
                throw new CoerceException("Value must be an array: " . $path);
            }
            $lst = [];
            foreach ($thriftVal as $i => $v) {
                if (is_null($v)) {
                    throw new CoerceException("List/Set element must not be null: " . $path . '[' . $i . ']');
                }
                $lst[] = self::coerce_($spec['elem'], $v, $path . '[' . $i . ']');
            }
            return $lst;
        case TType::MAP:
            if (!is_array($thriftVal)) {
                throw new CoerceException("Value must be an associative array: " . $path);
            }
            $map = [];
            foreach ($thriftVal as $k => $v) {
                if (is_null($v)) {
                    throw new CoerceException("Map value must not be null: " . $path . '->' . $k);
                }
                $map[self::coerce_($spec['key'], $k, $path . '->' . $k)] =
                    self::coerce_($spec['val'], $v, $path . '->' . $k);
            }
            return $map;
        case TType::VOID:
            if (is_array($thriftVal)) {
                throw new CoerceException("Void value must not be an array: " . $path);
            }
            return null;
        case TType::BOOL:
            if (is_array($thriftVal)) {
                throw new CoerceException("Bool value must not be an array: " . $path);
            }
            return (bool)$thriftVal;
        case TType::BYTE:
        case TType::I08:
        case TType::I16:
        case TType::I32:
        case TType::I64:
            if (is_array($thriftVal)) {
                throw new CoerceException("Int value must not be an array: " . $path);
            }
            return (int)$thriftVal;
        case TType::DOUBLE:
            if (is_array($thriftVal)) {
                throw new CoerceException("Double value must not be an array: " . $path);
            }
            return (float)$thriftVal;
        default:
            if (is_array($thriftVal)) {
                throw new CoerceException("String value must not be an array: " . $path);
            }
            return (string)$thriftVal;
        }
    }
}
