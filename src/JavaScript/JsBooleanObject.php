<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp\JavaScript;

/**
 * JavaScript Boolean() Object
 */
final class JsBooleanObject implements JsObjectInterface
{
    public function __construct(private bool $value)
    {
    }

    /**
     * @return bool
     */
    public function getValue(): bool
    {
        return $this->value;
    }

    /**
     * @param bool $value
     */
    public function setValue(bool $value): void
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }
}