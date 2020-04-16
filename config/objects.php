<?php
declare(strict_types=1);

return [
    'windbreaker' => [
        'name' => "Windbreaker",
    ],
    'overcoat' => [
        'name' => "Overcoat",
    ],
    'scarf' => [
        'name' => "Scarf",
    ],
    'wool-blanket' => [
        'name' => "Wool Blanket",
    ],
    'linen-blanket' => [
        'name' => "Linen Blanket",
    ],
    'tv-remote' => [
        'name' => "TV Remote",
    ],
    'digital-remote' => [
        'name' => "Digital Box Remote",
    ],
    'fridge' => [
        'name' => "Fridge",
        'attributes' => [
            "container",
            "heavy",
        ],
        'objects' => [
            "red-pepper",
            "yellow-pepper" => 3,
            "cheddar-cheese" => 2,
            "milk" => 2,
        ],
    ],
    'freezer' => [
        'name' => "Freezer",
        'attributes' => [
            "container",
            "heavy",
        ],
        'objects' => [
            "phish-food",
            "peanut-butter-cup",
        ],
    ],
    'red-pepper' => [
        'name' => "Red Pepper",
        'attributes' => [
            "edible",
        ],
    ],
    'yellow-pepper' => [
        'name' => "Yellow Pepper",
        'attributes' => [
            "edible",
        ],
    ],
    'phish-food' => [
        'name' => "Ben & Jerry's Phish Food",
        'attributes' => [
            "edible",
        ],
    ],
    'peanut-butter-cup' => [
        'name' => "Ben & Jerry's Peanut Butter Cup",
        'attributes' => [
            "edible",
        ],
    ],
    'hairdryer' => [
        'name' => "Hairdryer",
        'use' => [
            'message' => "You blow hot air directly into your face for 5 minutes. It's pleasant.",
            'xp' => 10,
        ],
    ],
    'couch' => [
        'name' => "Couch",
        'attributes' => [
            "heavy",
        ],
        'use' => [
            'location' => ["room"],
            'message' => "You have a bit of a sit down. Thrilling stuff.",
            'xp' => 10,
        ],
    ],
    'entertainment-unit' => [
        'name' => "Entertainment Unit",
        'attributes' => [
            "container",
            "heavy",
        ],
    ],
    'television' => [
        'name' => "Television",
        'attributes' => [
            "affixed",
        ],
    ],
    'slice-of-bread' => [
        'name' => "Slice of Bread",
        'attributes' => [
            "edible",
        ],
    ],
    'pack-of-crisps' => [
        'name' => "Pack of Crisps",
        'attributes' => [
            "edible",
        ],
    ],
    'crisp-half-sandwich' => [
        'name' => "Crisp Half-Sandwich",
        'attributes' => [
            "edible",
        ],
    ],
    'crisp-sandwich' => [
        'name' => "Crisp Sandwich",
        'attributes' => [
            "edible",
        ],
    ],
    'cheddar-cheese' => [
        'name' => "Cheddar Cheese",
        'portions' => 4,
        'attributes' => [
            "edible",
        ],
    ],
    'cheddar-cheese-half-sandwich' => [
        'name' => "Cheddar Cheese Half-Sandwich",
        'attributes' => [
            "edible",
        ],
    ],
    'cheddar-cheese-sandwich' => [
        'name' => "Cheddar Cheese Sandwich",
        'attributes' => [
            "edible",
        ],
    ],
    'milk' => [
        'name' => "Milk",
        'portions' => 10,
        'attributes' => [
            "edible",
        ],
    ],
    'coco-pops' => [
        'name' => "Coco Pops",
        'portions' => 12,
        'attributes' => [
            "edible",
        ],
    ],
    'bowl-of-coco-pops' => [
        'name' => "Bowl of Coco Pops",
        'attributes' => [
            "edible",
        ],
    ],
    'pantry' => [
        'name' => "Pantry",
        'attributes' => [
            "container",
            "heavy",
            "affixed",
        ],
        'objects' => [
            "coco-pops",
        ],
    ],
    'step-ladder' => [
        'name' => "Step Ladder",
    ],
    'deployed-step-ladder' => [
        'name' => "Deployed Step Ladder",
        'attributes' => [
            "dangerous",
        ],
    ],
    'bed' => [
        'name' => "Bed",
        'attributes' => [
            "heavy",
        ],
        'use' => [
            'message' => "You sleep until morning.",
            'xp' => 10,
        ],
    ],
    'letter-box' => [
        'name' => "Letter Box",
        'attributes' => [
            "container",
            "affixed",
        ],
        'objects' => [
            "quarantine-extension-notice"
        ],
    ],
    'quarantine-extension-notice' => [
        'name' => "Quarantine Extension Notice",
    ],
    'covid-19-cure' => [
        'name' => "Covid-19 Cure",
    ],
    'telephone' => [
        'name' => "Telephone",
    ],
    'dented-letter-box' => [
        'name' => "Severely Dented Letter Box",
        'attributes' => [
            "affixed",
        ],
    ],
];
