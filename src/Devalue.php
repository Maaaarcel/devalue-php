<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp;

use ArrayObject;
use DateTime;
use Maaaarcel\DevaluePhp\JavaScript\JsTypeInterface;
use Maaaarcel\DevaluePhp\JavaScript\JsValue;
use stdClass;

final class Devalue
{
    /**
     * Parses the passed serialized value. Note: Arrays will be returned as an `ArrayObject` and objects will be
     * returned as an `stdClass`
     *
     * @param string $serialized string to parse
     *
     * @return int|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue|null
     *
     * @throws DevalueException
     */
    public static function parse(
        string $serialized
    ): int|null|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue {
        return (new Parser())->parse($serialized);
    }

    /**
     * Stringifies the passed value.
     *
     * @param mixed $value value to stringify
     *
     * @return string
     *
     * @throws DevalueException
     */
    public static function stringify(mixed $value): string
    {
        return (new Stringifier())->stringify($value);
    }
}