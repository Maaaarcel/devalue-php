<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp;

use ArrayObject;
use Closure;
use DateTimeInterface;
use JsonSerializable;
use Maaaarcel\DevaluePhp\JavaScript\JsBigInt;
use Maaaarcel\DevaluePhp\JavaScript\JsMap;
use Maaaarcel\DevaluePhp\JavaScript\JsObjectInterface;
use Maaaarcel\DevaluePhp\JavaScript\JsRegExp;
use Maaaarcel\DevaluePhp\JavaScript\JsSet;
use Maaaarcel\DevaluePhp\JavaScript\JsValue;
use SplObjectStorage;
use stdClass;

/**
 * @internal
 */
final class Stringifier
{
    private const JS_MAX_INT = 9007199254740991;

    private array $stringified = [];
    private SplObjectStorage $objectIndexes;
    private array $primitiveIndexes = [];
    private array $keys = [];
    private int $p = 0;

    public function __construct()
    {
        $this->objectIndexes = new SplObjectStorage();
    }

    /**
     * @throws DevalueException
     */
    public function stringify(mixed $value): string
    {
        $index = $this->flatten($value);

        if ($index < 0) {
            return strval($index);
        }

        ksort($this->stringified);
        return str_replace(
            '/',
            '\\u002F',
            json_encode($this->stringified, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)
        );
    }

    /**
     * @throws DevalueException
     */
    private function flatten(mixed $thing): mixed
    {
        if ($thing instanceof Closure) {
            throw new DevalueException('Cannot stringify a function', 2);
        }

        $serializedThing = '';
        if (is_object($thing)) {
            if ($this->objectIndexes->offsetExists($thing)) {
                return $this->objectIndexes[$thing];
            }
        } else {
            $serializedThing = is_array($thing) ? serialize($thing) : $thing;
            if (isset($this->primitiveIndexes[$serializedThing])) {
                return $this->primitiveIndexes[$serializedThing];
            }
        }

        if ($thing instanceof JsValue) {
            return $thing->value;
        }

        if (is_int($thing) || is_float($thing)) {
            if (is_nan($thing)) {
                return JsValue::Nan->value;
            }

            if (is_infinite($thing) && $thing > 0) {
                return JsValue::PositiveInfinity->value;
            }

            if (is_infinite($thing) && $thing < 0) {
                return JsValue::NegativeInfinity->value;
            }
        }

        $index = $this->p;
        $this->p++;

        if (is_object($thing)) {
            $this->objectIndexes->offsetSet($thing, $index);
        } else {
            $this->primitiveIndexes[$serializedThing] = $index;
        }

        if (self::isPrimitive($thing)) {
            $this->stringified[$index] = $thing;
        } else {
            $out = [];
            switch (true) {
                case $thing instanceof JsObjectInterface:
                    array_push($out, 'Object', $thing->getValue());
                    break;

                case $thing instanceof DateTimeInterface:
                    array_push($out, 'Date', $thing->format('Y-m-d\TH:i:s.v\Z'));
                    break;

                case (is_float($thing) || is_int($thing)) && ($thing > self::JS_MAX_INT):
                    array_push($out, 'BigInt', (string)$thing);
                    break;

                case $thing instanceof JsBigInt:
                    array_push($out, 'BigInt', (string)$thing->getValue());
                    break;

                case $thing instanceof JsMap:
                    $out[] = 'Map';
                    foreach ($thing as $key => $value) {
                        $mapKey = self::isPrimitive($key) ? $key : '...';
                        $this->keys[] = '.get(' . $mapKey . ')';
                        $out[] = self::flatten($key);
                        $out[] = self::flatten($value);
                    }
                    break;

                case $thing instanceof JsSet:
                    $out[] = 'Set';
                    foreach ($thing as $value) {
                        $out[] = self::flatten($value);
                    }
                    break;

                case $thing instanceof JsRegExp:
                    $source = $thing->getSource();
                    $flags = $thing->getFlags();
                    array_push($out, 'RegExp', $source);
                    if ($flags) {
                        $out[] = $flags;
                    }
                    break;

                case (is_array($thing) && array_is_list($thing)) || $thing instanceof ArrayObject:
                    for ($i = 0; $i < count($thing); $i += 1) {
                        if ((is_array($thing) && array_key_exists($i, $thing))
                            || $thing->offsetExists($i)) {
                            $this->keys[] = '[' . $i . ']';
                            $out[] = self::flatten($thing[$i]);
                            array_pop($this->keys);
                        } else {
                            $out[] = JsValue::Hole->value;
                        }
                    }

                    break;

                default:
                    if (is_object($thing)) {
                        if ($thing instanceof JsonSerializable) {
                            $thing = $thing->jsonSerialize();
                        } else {
                            if ($thing instanceof stdClass) {
                                $thing = (array)$thing;
                            } else {
                                throw new DevalueException(
                                    'Objects need to implement the `JsonSerializable` to get serialized.',
                                    3
                                );
                            }
                        }
                    }

                    if ($thing[JsObjectInterface::NULL_PROTOTYPE_KEY] ?? false) {
                        unset($thing[JsObjectInterface::NULL_PROTOTYPE_KEY]);

                        $out[] = 'null';
                        foreach ($thing as $key => $val) {
                            $this->keys[] = '.' . $key;
                            array_push($out, $key, $this->flatten($val));
                            array_pop($this->keys);
                        }
                    } else {
                        $out = new stdClass();
                        foreach ($thing as $key => $val) {
                            $this->keys[] = '.' . $key;
                            $out->{$key} = self::flatten($val);
                            array_pop($this->keys);
                        }
                    }
            }

            $this->stringified[$index] = $out;
        }

        return $index;
    }

    private static function isPrimitive(mixed $value): bool
    {
        return is_string($value)
            || (is_int($value) && $value <= self::JS_MAX_INT)
            || (is_float($value) && $value <= self::JS_MAX_INT)
            || is_bool($value)
            || is_null($value);
    }
}