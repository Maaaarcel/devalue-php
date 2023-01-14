<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp\JavaScript;

/**
 * JavaScript RegExp() Object
 */
final class JsRegExp implements JsTypeInterface
{
    public function __construct(private string $source = '', private string $flags = '')
    {
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getFlags(): string
    {
        return $this->flags;
    }

    /**
     * @param string $flags
     */
    public function setFlags(string $flags): void
    {
        $this->flags = $flags;
    }

    public function __toString(): string
    {
        return '/' . $this->source . '/' . $this->flags;
    }
}