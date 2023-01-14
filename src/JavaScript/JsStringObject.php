<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp\JavaScript;

/**
 * JavaScript String() Object
 */
final class JsStringObject implements JsObjectInterface
{
    public function __construct(private string $value)
    {
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}