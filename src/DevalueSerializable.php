<?php

declare(strict_types=1);

namespace Maaaarcel\DevaluePhp;

interface DevalueSerializable
{
    /**
     * @return string the type name for serializing / deserializing
     */
    static function devalueType(): string;

    static function devalueParse(array $serialized): static;

    function devalueSerialize(): array;
}