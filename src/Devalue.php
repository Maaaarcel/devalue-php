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
     * Converts a devalue array to an un-flattened php array. Useful, if your devalue data is inside a property of a
     * regular json payload.
     *
     * @param stdClass|array|int $parsed
     *
     * @return int|null|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue
     *
     * @throws DevalueException
     */
    public static function unflatten(
        stdClass|array|int $parsed
    ): int|null|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue {
        return (new Parser())->unflatten($parsed);
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