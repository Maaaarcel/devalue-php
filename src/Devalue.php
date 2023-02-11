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
     * @var class-string<DevalueSerializable>[]
     */
    private static array $customTypes = [];

    /**
     * @param class-string<DevalueSerializable>[] $customTypes
     * @return void
     */
    public static function registerCustomTypes(array $customTypes): void
    {
        self::$customTypes = $customTypes;
    }

    /**
     * @param class-string<DevalueSerializable> $customType
     * @return void
     */
    public static function registerCustomType(string $customType): void
    {
        self::$customTypes[] = $customType;
    }

    /**
     * @param class-string<DevalueSerializable> $customType
     * @return void
     */
    public static function unregisterCustomType(string $customType): void
    {
        self::$customTypes = array_filter(self::$customTypes, fn (string $item) => $item !== $customType);
    }

    /**
     * Parses the passed serialized value. Note: Arrays will be returned as an `ArrayObject` and objects will be
     * returned as an `stdClass`
     *
     * @param string $serialized string to parse
     * @param class-string<DevalueSerializable>[] $customTypes
     *
     * @return int|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue|DevalueSerializable|null
     *
     * @throws DevalueException
     */
    public static function parse(
        string $serialized,
        array $customTypes = []
    ): int|null|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue|DevalueSerializable {
        $parser = new Parser();
        $parser->registerCustomTypes(array_merge($customTypes, self::$customTypes));
        return $parser->parse($serialized);
    }

    /**
     * Converts a devalue array to an un-flattened php array. Useful, if your devalue data is inside a property of a
     * regular json payload.
     *
     * @param stdClass|array|int $parsed
     * @param class-string<DevalueSerializable>[] $customTypes
     *
     * @return int|null|float|string|bool|stdClass|ArrayObject<int, mixed>|DateTime|JsTypeInterface|JsValue
     *
     * @throws DevalueException
     */
    public static function unflatten(
        stdClass|array|int $parsed,
        array $customTypes = []
    ): int|null|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue {
        $parser = new Parser();
        $parser->registerCustomTypes(array_merge($customTypes, self::$customTypes));
        return $parser->unflatten($parsed);
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
        $stringifier = new Stringifier();
        return $stringifier->stringify($value);
    }
}