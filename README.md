# devalue-php

This is an PHP port of [devalue](https://github.com/Rich-Harris/devalue), a JavaScript library for stringifying and
parsing of data that is too complex for JSON. Take a look at the original repo for all features.

All credit for the parsing and stringifying logic goes to the creator and contributors of
the [devalue](https://github.com/Rich-Harris/devalue) library. The logic in this implementation is mostly the same, with
some slight changes to improve the performance with PHP.

## Installation

Install it using [composer](https://packagist.org/packages/maaaarcel/devalue-php):

```shell
composer require maaaarcel/devalue-php
```

## Usage

The `Devalue` class has the `stringify`, `parse` and `unflatten` methods, used for stringifying and parsing on the backend.

```php
use Maaaarcel\DevaluePhp\Devalue;
use Maaaarcel\DevaluePhp\JavaScript\JsBigInt;
use Maaaarcel\DevaluePhp\JavaScript\JsNumberObject;
use Maaaarcel\DevaluePhp\JavaScript\JsRegExp;
use DateTime;

$obj = new stdClass();
$obj->self = $obj;
$data = [
    'recursiveObject' => $obj,
    'regex' => new JsRegExp('.+', 'g'),
    'date' => new DateTime('2023-01-16'),
    'autoConverted' => INF,
    'undefined' => JsValue::Undefined,
    'normalNumber' => 1,
    'array' => [new JsBigInt(1), new JsNumberObject(2)]
];
$dataStr = Devalue::stringify($data);
// => '[{"recursiveObject":1,"regex":2,"date":3,"autoConverted":-4,"undefined":-1,"normalNumber":4,"array":5},{"self":1},["RegExp",".+","g"],["Date","2023-01-16T00:00:00.000Z"],1,[6,7],["BigInt","1"],["Object",2]]'

$parsedData = Devalue::parse($dataStr);
// => object(stdClass)#507 (7) {
//   ["recursiveObject"]=>
//   object(stdClass)#505 (1) {
//     ["self"]=>
//     *RECURSION*
//   }
//   ["regex"]=>
//   object(Maaaarcel\DevaluePhp\JavaScript\JsRegExp)#506 (2) {
//     ["source":"Maaaarcel\DevaluePhp\JavaScript\JsRegExp":private]=>
//     string(2) ".+"
//     ["flags":"Maaaarcel\DevaluePhp\JavaScript\JsRegExp":private]=>
//     string(1) "g"
//   }
//   ["date"]=>
//   object(DateTime)#504 (3) {
//     ["date"]=>
//     string(26) "2023-01-16 00:00:00.000000"
//     ["timezone_type"]=>
//     int(2)
//     ["timezone"]=>
//     string(1) "Z"
//   }
//   ["autoConverted"]=>
//   enum(Maaaarcel\DevaluePhp\JavaScript\JsValue::PositiveInfinity)
//   ["undefined"]=>
//   enum(Maaaarcel\DevaluePhp\JavaScript\JsValue::Undefined)
//   ["normalNumber"]=>
//   int(1)
//   ["array"]=>
//   object(ArrayObject)#503 (1) {
//     ["storage":"ArrayObject":private]=>
//     array(2) {
//       [0]=>
//       object(Maaaarcel\DevaluePhp\JavaScript\JsBigInt)#501 (1) {
//         ["value":"Maaaarcel\DevaluePhp\JavaScript\JsBigInt":private]=>
//         int(1)
//       }
//       [1]=>
//       object(Maaaarcel\DevaluePhp\JavaScript\JsNumberObject)#500 (1) {
//         ["value":"Maaaarcel\DevaluePhp\JavaScript\JsNumberObject":private]=>
//         int(2)
//       }
//     }
//   }
// }

// if your devalue payload was inside a normal JSON string, you can use the value from json_decode with the `unflatten`
// method like so:

$json = '{
    "type": "data",
    "data": [{...devalue data...}]
}';
$payload = json_decode($json);

$data = Devalue::unflatten($payload->data);
```

`Devalue::stringify` can handle the following data types:

- `\Maaaarcel\DevaluePhp\JavaScript\JsBooleanObject`
- `\Maaaarcel\DevaluePhp\JavaScript\JsNumberObject`
- `\Maaaarcel\DevaluePhp\JavaScript\JsStringObject`
- `\Maaaarcel\DevaluePhp\JavaScript\JsBigInt`
- `\Maaaarcel\DevaluePhp\JavaScript\JsMap`
- `\Maaaarcel\DevaluePhp\JavaScript\JsSet`
- `\Maaaarcel\DevaluePhp\JavaScript\JsRegExp`
- `int` (gets automatically converted to a `BigInt`, if the number is greater than `9007199254740991`)
- `float`
- `string`
- `bool`
- `null`
- `NAN` (gets converted to `NaN`)
- `INF` (gets converted to `Infinity` / `-Infinity`)
- `\DateTimeInterface` (gets converted to `Date()`)
- `\JsonSerializable` (gets converted to a JS object)
- `\stdClass` (gets converted to a JS object)
- `\ArrayObject` (gets converted to a JS array)
- `[1, 2, 3] (list)` (gets converted to a JS array)
- `['a' => 1, 'b' => 2, 'c' => 3] (assoc array)` (gets converted to a JS object)

The `Devalue::parse` and `Devalue::unflatten` methods convert some JavaScript values into objects/enum values for a more 
accurate representation of the data. Those objects/enum values can also be used to pass those JavaScript values when 
using `Devalue::stringify`.

Here is a list of the conversions (left: JavaScript value, right: PHP value):

- `objects`: `\stdClass()`
- `arrays`: `\ArrayObject()`
- `Date()`: `\DateTime()`
- `BigInt()`: `\Maaaarcel\DevaluePhp\JavaScript\JsBigInt()`
- `Boolean()`: `\Maaaarcel\DevaluePhp\JavaScript\JsBooleanObject()`
- `Number()`: `\Maaaarcel\DevaluePhp\JavaScript\JsNumberObject()`
- `String()`: `\Maaaarcel\DevaluePhp\JavaScript\JsStringObject()`
- `RegExp()`: `\Maaaarcel\DevaluePhp\JavaScript\JsRegExp()`
- `Map()`: `\Maaaarcel\DevaluePhp\JavaScript\JsMap()`
- `Set()`: `\Maaaarcel\DevaluePhp\JavaScript\JsSet()`
- `undefined`: `\Maaaarcel\DevaluePhp\JavaScript\JsValue::Undefined`
- `NaN`: `\Maaaarcel\DevaluePhp\JavaScript\JsValue::Nan`
- `-0`: `\Maaaarcel\DevaluePhp\JavaScript\JsValue::NegativeZero`
- `Infinity`: `\Maaaarcel\DevaluePhp\JavaScript\JsValue::PositiveInfinity`
- `-Infinity`: `\Maaaarcel\DevaluePhp\JavaScript\JsValue::NegativeInfinity`

Stringify converts some specific PHP values for better compatability / usability before creating the string:

To consume your data in your frontend code, you have to use the
original [devalue](https://github.com/Rich-Harris/devalue) library.

## Performance

The performance is obviously worse than basic `json_encode` and `json_decode`. Currently, `devalue-php` is about 50x
slower than json for stringifying and 20x slower for parsing. Feel free to contribute, if you find a way to improve the
performance!

Here is a benchmark run with [phpbench](https://github.com/phpbench/phpbench) :

`phpbench run tests/Benchmark --report aggregate`

```
+--------------+-----------------------+-----+------+-----+----------+----------+--------+
| benchmark    | subject               | set | revs | its | mem_peak | mode     | rstdev |
+--------------+-----------------------+-----+------+-----+----------+----------+--------+
| DevalueBench | benchDevalueStringify |     | 1000 | 8   | 1.613mb  | 26.696μs | ±6.39% |
| DevalueBench | benchJsonEncode       |     | 1000 | 8   | 1.613mb  | 0.506μs  | ±7.06% |
| DevalueBench | benchDevalueParse     |     | 1000 | 8   | 1.613mb  | 35.590μs | ±8.40% |
| DevalueBench | benchJsonDecode       |     | 1000 | 8   | 1.613mb  | 1.996μs  | ±2.11% |
+--------------+-----------------------+-----+------+-----+----------+----------+--------+
```

## License

[MIT](./LICENSE)