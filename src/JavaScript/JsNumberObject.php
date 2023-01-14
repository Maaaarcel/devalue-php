<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp\JavaScript;

/**
 * JavaScript Number() Object
 */
final class JsNumberObject implements JsObjectInterface
{
    public function __construct(private int|float $value)
    {
    }

    /**
     * @return float|int
     */
    public function getValue(): float|int
    {
        return $this->value;
    }

    /**
     * @param float|int $value
     */
    public function setValue(float|int $value): void
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}