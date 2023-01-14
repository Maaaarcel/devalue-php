<?php

declare(strict_types=1);

namespace Maaaarcel\Tests\Benchmark;

use Maaaarcel\DevaluePhp\Devalue;
use Maaaarcel\DevaluePhp\DevalueException;

final class DevalueBench
{

    private const SUBJECT = [
        'key1' => [1, 2, 3, 400000],
        'key2' => [
            'key3' => [
                'key4' => [
                    'key5' => 1,
                    'key6' => 2
                ],
                'key7' => 3
            ],
            'key8' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata'
        ],
        'key9' => [
            'key10' => [
                [
                    [
                        'key11' => [
                            'key12' => [
                                [
                                    [
                                        [
                                            [
                                                400000
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    /**
     * @Revs(1000)
     * @Iterations(8)
     * @throws DevalueException
     */
    public function benchDevalueStringify(): void
    {
        Devalue::stringify(self::SUBJECT);
    }

    /**
     * @Revs(1000)
     * @Iterations(8)
     */
    public function benchJsonEncode(): void
    {
        json_encode(self::SUBJECT);
    }

    /**
     * @Revs(1000)
     * @Iterations(8)
     * @throws DevalueException
     */
    public function benchDevalueParse(): void
    {
        Devalue::parse(
            '[{"key1":1,"key2":6,"key9":10},[2,3,4,5],1,2,3,400000,{"key3":7,"key8":9},{"key4":8,"key7":4},{"key5":2,"key6":3},"Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata",{"key10":11},[12],[13],{"key11":14},{"key12":15},[16],[17],[18],[19],[5]]'
        );
    }

    /**
     * @Revs(1000)
     * @Iterations(8)
     */
    public function benchJsonDecode(): void
    {
        json_decode(
            '{"key1":[1,2,3,400000],"key2":{"key3":{"key4":{"key5":1,"key6":2},"key7":3},"key8":"Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata"},"key9":{"key10":[[{"key11":{"key12":[[[[[400000]]]]]}}]]}}'
        );
    }
}