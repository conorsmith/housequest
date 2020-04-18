<?php
declare(strict_types=1);

return [
    [
        'input' => ["slice-of-bread", "pack-of-crisps"],
        'output' => "crisp-half-sandwich",
    ],
    [
        'input' => [
            "slice-of-bread" => ['quantity' => 2],
            "pack-of-crisps"
        ],
        'output' => "crisp-sandwich",
    ],
    [
        'input' => [
            "slice-of-bread",
            "cheddar-cheese" => ['portions' => 1],
        ],
        'output' => "cheddar-cheese-half-sandwich",
    ],
    [
        'input' => [
            "slice-of-bread" => ['quantity' => 2],
            "cheddar-cheese" => ['portions' => 1],
        ],
        'output' => "cheddar-cheese-sandwich",
    ],
    [
        'input' => [
            "milk"      => ['portions' => 1],
            "coco-pops" => ['portions' => 1],
        ],
        'output' => "bowl-of-coco-pops",
    ],
    [
        'input' => [
            "dining-chair" => ['quantity' => 3],
        ],
        'output' => "chair-ladder",
        'location' => "room",
    ],
    [
        'input' => [
            "chair-ladder",
        ],
        'output' => [
            "dining-chair" => ['quantity' => 3],
        ],
    ],
];
