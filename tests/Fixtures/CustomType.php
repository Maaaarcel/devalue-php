<?php

declare(strict_types=1);

namespace Maaaarcel\Tests\Fixtures;

use Maaaarcel\DevaluePhp\DevalueSerializable;

class CustomType implements DevalueSerializable
{

    public function __construct(
        public readonly string $field1 = '',
        public readonly string $field2 = ''
    )
    {
    }

    static function devalueType(): string
    {
        return 'CustomType';
    }

    static function devalueParse(array $serialized): static
    {
        return new self($serialized['field1'], $serialized['field2']);
    }

    function devalueSerialize(): array
    {
        return [
            'field1' => $this->field1,
            'field2' => $this->field2
        ];
    }
}