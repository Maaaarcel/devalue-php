<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp;

use ArrayObject;
use DateTime;
use Exception;
use Maaaarcel\DevaluePhp\JavaScript\JsBigInt;
use Maaaarcel\DevaluePhp\JavaScript\JsBooleanObject;
use Maaaarcel\DevaluePhp\JavaScript\JsMap;
use Maaaarcel\DevaluePhp\JavaScript\JsNumberObject;
use Maaaarcel\DevaluePhp\JavaScript\JsObjectInterface;
use Maaaarcel\DevaluePhp\JavaScript\JsRegExp;
use Maaaarcel\DevaluePhp\JavaScript\JsSet;
use Maaaarcel\DevaluePhp\JavaScript\JsStringObject;
use Maaaarcel\DevaluePhp\JavaScript\JsTypeInterface;
use Maaaarcel\DevaluePhp\JavaScript\JsValue;
use SplFixedArray;
use stdClass;
use ValueError;

/**
 * @internal
 */
final class Parser
{
    private ?SplFixedArray $hydrated = null;

    private stdClass|array|int $values;

    /**
     * @throws DevalueException
     */
    public function parse(
        string $serialized
    ): int|null|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue {
        return $this->unflatten(json_decode($serialized));
    }


    /**
     * @throws DevalueException
     * @throws Exception
     */
    public function unflatten(
        stdClass|array|int $parsed
    ): int|null|float|string|bool|stdClass|ArrayObject|DateTime|JsTypeInterface|JsValue {
        $this->values = $parsed;

        if (is_int($parsed)) {
            return $this->hydrate($parsed, true);
        }

        if (!is_array($parsed) || count($parsed) === 0) {
            throw new Exception('Invalid input', 1);
        }

        return $this->hydrate(0);
    }


    /**
     * @throws DevalueException
     * @throws Exception
     */
    private function hydrate(
        int $index,
        bool $standalone = false
    ): mixed {
        try {
            return JsValue::from($index);
        } catch (ValueError) {
        }

        if ($standalone) {
            throw new DevalueException('Invalid input', 1);
        }

        if (is_null($this->hydrated)) {
            $this->hydrated = new SplFixedArray(count((array)$this->values));
        }

        if ($this->hydrated->offsetExists($index)) {
            return $this->hydrated->offsetGet($index);
        }

        $value = $this->values[$index];

        if (!$value || (!is_array($value) && !($value instanceof stdClass))) {
            $this->hydrated[$index] = $value;
        } else {
            if (is_array($value) && array_is_list($value)) {
                if (is_string($value[0])) {
                    $type = $value[0];

                    switch ($type) {
                        case 'Date':
                            $this->hydrated[$index] = new DateTime($value[1]);
                            break;

                        case 'Set':
                            $set = new JsSet();
                            $this->hydrated[$index] = $set;
                            for ($i = 1; $i < count($value); $i += 1) {
                                $set->add($this->hydrate($value[$i]));
                            }
                            $set->removeDuplicates();
                            break;

                        case 'Map':
                            $map = new JsMap();
                            $this->hydrated[$index] = $map;
                            for ($i = 1; $i < count($value); $i += 2) {
                                $map[$this->hydrate($value[$i])] = $this->hydrate($value[$i + 1]);
                            }
                            break;

                        case 'RegExp':
                            $this->hydrated[$index] = new JsRegExp($value[1], $value[2] ?? '');
                            break;

                        case 'BigInt':
                            $this->hydrated[$index] = new JsBigInt((int)$value[1]);
                            break;

                        case 'Object':
                            $objectValue = $value[1];
                            $this->hydrated[$index] = match (true) {
                                is_bool($objectValue) => new JsBooleanObject($objectValue),
                                is_int($objectValue) || is_float($objectValue) => new JsNumberObject($objectValue),
                                default => new JsStringObject($objectValue)
                            };
                            break;

                        case 'null':
                            $object = new stdClass();
                            $object->{JsObjectInterface::NULL_PROTOTYPE_KEY} = true;
                            $this->hydrated[$index] = $object;
                            for ($i = 1; $i < count($value); $i += 2) {
                                $object->{$value[$i]} = $this->hydrate($value[$i + 1]);
                            }
                            break;
                    }
                } else {
                    $array = new ArrayObject();
                    $this->hydrated[$index] = $array;

                    for ($i = 0; $i < count($value); $i += 1) {
                        $n = $value[$i];
                        if ($n === JsValue::Hole->value) {
                            continue;
                        }

                        $array[$i] = $this->hydrate($n);
                    }
                }
            } else {
                $object = new stdClass();
                $this->hydrated[$index] = $object;

                foreach ($value as $key => $val) {
                    $object->{$key} = $this->hydrate($val);
                }
            }
        }

        return $this->hydrated[$index];
    }
}