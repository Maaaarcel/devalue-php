<?php

declare(strict_types=1);

namespace Maaaarcel\Tests\Unit;

use ArrayObject;
use DateTimeImmutable;
use JsonSerializable;
use Maaaarcel\DevaluePhp\Devalue;
use Maaaarcel\DevaluePhp\DevalueException;
use Maaaarcel\DevaluePhp\JavaScript\JsBigInt;
use Maaaarcel\DevaluePhp\JavaScript\JsBooleanObject;
use Maaaarcel\DevaluePhp\JavaScript\JsMap;
use Maaaarcel\DevaluePhp\JavaScript\JsNumberObject;
use Maaaarcel\DevaluePhp\JavaScript\JsObjectInterface;
use Maaaarcel\DevaluePhp\JavaScript\JsRegExp;
use Maaaarcel\DevaluePhp\JavaScript\JsSet;
use Maaaarcel\DevaluePhp\JavaScript\JsStringObject;
use Maaaarcel\DevaluePhp\JavaScript\JsValue;
use Maaaarcel\Tests\Fixtures\CustomType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DevalueTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    public function fixturesProvider(): array
    {
        return [
            '[Basics] Number' => [42, '[42]'],
            '[Basics] Negative number' => [-42, '[-42]'],
            '[Basics] Negative zero' => [JsValue::NegativeZero, '-6'],
            '[Basics] Positive decimal' => [0.1, '[0.1]'],
            '[Basics] Negative decimal' => [-0.1, '[-0.1]'],
            '[Basics] String' => ['woo!!!', '["woo!!!"]'],
            '[Basics] Boolean' => [true, '[true]'],
            '[Basics] Number object' => [new JsNumberObject(42), '[["Object",42]]'],
            '[Basics] String object' => [new JsStringObject('yar'), '[["Object","yar"]]'],
            '[Basics] Boolean object' => [new JsBooleanObject(false), '[["Object",false]]'],
            '[Basics] Undefined' => [JsValue::Undefined, '-1'],
            '[Basics] Null' => [null, '[null]'],
            '[Basics] NaN' => [JsValue::Nan, '-3'],
            '[Basics] Infinity' => [JsValue::PositiveInfinity, '-4'],
            '[Basics] RegExp' => [new JsRegExp('regexp', 'gim'), '[["RegExp","regexp","gim"]]'],
            '[Basics] Date' => [
                new DateTimeImmutable('2001-09-09T01:46:40.000Z'),
                '[["Date","2001-09-09T01:46:40.000Z"]]'
            ],
            '[Basics] Array' => [new ArrayObject(['a', 'b', 'c']), '[[1,2,3],"a","b","c"]'],
            '[Basics] Object' => (function () {
                $obj = new stdClass();
                $obj->foo = 'bar';
                $obj->{'x-y'} = 'z';
                return [$obj, '[{"foo":1,"x-y":2},"bar","z"]'];
            })(),
            '[Basics] Set' => [new JsSet([1, 2, 3]), '[["Set",1,2,3],1,2,3]'],
            '[Basics] Map' => [new JsMap([['a', 'b']]), '[["Map",1,2],"a","b"]'],
            '[Basics] BigInt' => [new JsBigInt(1), '[["BigInt","1"]]'],
            '[Basics] Null prototype object' => (function () {
                $obj = new stdClass();
                $obj->self = $obj;
                $obj->{JsObjectInterface::NULL_PROTOTYPE_KEY} = true;
                return [$obj, '[["null","self",0]]'];
            })(),

            '[Strings] Newline' => ['a\nb', '["a\\\\nb"]'],
            '[Strings] Double quotes' => ['"yar"', '["\\"yar\\""]'],
            // those do not work properly in PHP
            //'[Strings] Lone low surrogate' => ['a\uDC00b', '["a\\uDC00b"]'],
            //'[Strings] Lone high surrogate' => ['a\uD800b', '["a\\uD800b"]'],
            //'[Strings] Two low surrogates' => ['a\uDC00\uDC00b', '["a\\uDC00\\uDC00b"]'],
            //'[Strings] Two high surrogates' => ['a\uD800\uD800b', '["a\\uD800\\uD800b"]'],
            //'[Strings] Surrogate pair' => ['𝌆', '[' . json_encode('𝌆') . ']'],
            //'[Strings] Surrogate pair in wrong order' => ['a\uDC00\uD800b', '["a\\uDC00\\uD800b"]'],
            //'[Strings] Nul' => ["\0", '["\\u0000"]'],
            '[Strings] Backslash' => ['\\', '["\\\\"]'],

            '[Cycles] Map (cyclical)' => (function () {
                $map = new JsMap();
                $map->set('self', $map);
                return [$map, '[["Map",1,0],"self"]'];
            })(),
            '[Cycles] Set (cyclical)' => (function () {
                $set = new JsSet();
                $set->add($set);
                $set->add(42);
                $set->removeDuplicates();
                return [$set, '[["Set",0,1],42]'];
            })(),
            '[Cycles] Array (cyclical)' => (function () {
                $arr = new ArrayObject();
                $arr[0] = $arr;
                return [$arr, '[[0]]'];
            })(),
            '[Cycles] Object (cyclical)' => (function () {
                $obj = new stdClass();
                $obj->self = $obj;
                return [$obj, '[{"self":0}]'];
            })(),
            '[Cycles] Object (cyclical / cross)' => (function () {
                $obj1 = new stdClass();
                $obj2 = new stdClass();
                $obj1->second = $obj2;
                $obj2->first = $obj1;
                return [new ArrayObject([$obj1, $obj2]), '[[1,2],{"second":2},{"first":1}]'];
            })(),

            '[Repetition] String' => [new ArrayObject(['a string', 'a string']), '[[1,1],"a string"]'],
            '[Repetition] Null ' => [new ArrayObject([null, null]), '[[1,1],null]'],
            '[Repetition] Object ' => (function () {
                $obj = new stdClass();
                return [new ArrayObject([$obj, $obj]), '[[1,1],{}]'];
            })(),

            '[XSS] Dangerous string' => [
                "</script><script src='https://evil.com/script.js'>alert('pwned')</script><script>",
                '["\\u003C\\u002Fscript\\u003E\\u003Cscript src=\'https:\\u002F\\u002Fevil.com\\u002Fscript.js\'\\u003Ealert(\'pwned\')\\u003C\\u002Fscript\\u003E\\u003Cscript\\u003E"]'
            ],
            '[XSS] Dangerous key' => (function () {
                $obj = new stdClass();
                $obj->{'<svg onload=alert("xss_works")>'} = 'bar';
                return [$obj, '[{"\\u003Csvg onload=alert(\\"xss_works\\")\\u003E":1},"bar"]'];
            })(),
            '[XSS] Dangerous regex' => [
                new JsRegExp("[</script><script>alert('xss')//]"),
                '[["RegExp","[\\u003C\\u002Fscript\\u003E\\u003Cscript\\u003Ealert(\'xss\')\\u002F\\u002F]"]]'
            ],
        ];
    }

    /**
     * @dataProvider fixturesProvider
     * @throws DevalueException
     */
    public function testStringify(mixed $valueToStringify, string $expectedValue): void
    {
        $this->assertSame($expectedValue, Devalue::stringify($valueToStringify));
    }

    /**
     * @throws DevalueException
     */
    public function testStringifyArrays(): void
    {
        $this->assertSame('[[1,2,3],"a","b","c"]', Devalue::stringify(['a', 'b', 'c']), 'Simple arrays get converted');
        $this->assertSame(
            '[{"foo":1,"x-y":2},"bar","z"]',
            Devalue::stringify(['foo' => 'bar', 'x-y' => 'z']),
            'Associative Arrays get converted'
        );

        $arr = [];
        $arr['self'] = &$arr;
        $this->assertSame('[{"self":0}]', Devalue::stringify($arr), 'Array self references');
    }

    /**
     * @throws DevalueException
     */
    public function testStringifySpecialValues()
    {
        $this->assertSame('-3', Devalue::stringify(NAN), 'NAN gets converted');
        $this->assertSame('-4', Devalue::stringify(INF), 'INF gets converted');
        $this->assertSame('-5', Devalue::stringify(-INF), '-INF gets converted');
    }

    public function testStringifyBigInt(): void
    {

        $this->assertSame(
            '[["BigInt","9007199254740992"]]',
            Devalue::stringify(9007199254740992),
            'Big integers are converted into BigInt'
        );
    }

    /**
     * @throws DevalueException
     */
    public function testStringifyJsonSerializable(): void
    {
        $this->assertSame(
            '[{"foo":1},"bar"]',
            Devalue::stringify(
                new class implements JsonSerializable {
                    public function jsonSerialize(): array
                    {
                        return [
                            'foo' => 'bar'
                        ];
                    }
                }
            ),
            'JsonSerializable objects get converted'
        );
    }

    /**
     * @throws DevalueException
     */
    public function testCustomTypes(): void
    {
        $customType = new CustomType('foo', 'bar');
        $serialized = Devalue::stringify($customType);
        $this->assertSame('[["CustomType",1],{"field1":2,"field2":3},"foo","bar"]', $serialized);

        $deserialized = Devalue::parse($serialized, [CustomType::class]);
        $this->assertInstanceOf(CustomType::class, $deserialized);
        $this->assertSame($deserialized->field1, 'foo');

        $serializedArray = Devalue::stringify([$customType, $customType]);
        $this->assertSame('[[1,1],["CustomType",2],{"field1":3,"field2":4},"foo","bar"]', $serializedArray);

        $deserializedArray = Devalue::parse($serializedArray, [CustomType::class]);
        $this->assertSame($deserializedArray[0], $deserializedArray[1]);
    }

    /**
     * @dataProvider fixturesProvider
     * @throws DevalueException
     */
    public function testParse(mixed $expectedValue, string $valueToParse): void
    {
        $this->assertEquals($expectedValue, Devalue::parse($valueToParse));
    }
}