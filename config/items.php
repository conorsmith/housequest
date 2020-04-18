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
    ],
    'freezer' => [
        'name' => "Freezer",
        'attributes' => [
            "container",
            "heavy",
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
        'use' => "You blow hot air directly into your face for 5 minutes. It's pleasant.",
    ],
    'couch' => [
        'name' => "Couch",
        'attributes' => [
            "heavy",
        ],
        'use' => [
            'location' => ["room"],
            'message' => "You have a bit of a sit down. Thrilling stuff.",
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
    ],
    'step-ladder' => [
        'name' => "Step Ladder",
        'states' => [
            'closed' => "",
            'open'   => [
                'label' => "Open",
                'attributes' => [
                    "dangerous",
                ],
            ],
        ],
    ],
    'bed' => [
        'name' => "Bed",
        'attributes' => [
            "heavy",
        ],
        'use' => [
            'message' => "You sleep until morning.",
        ],
    ],
    'letter-box' => [
        'name' => "Letter Box",
        'attributes' => [
            "container",
            "affixed",
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
    'toilet' => [
        'name' => "Toilet",
        'use' => "You relieve yourself.",
        'attributes' => [
            "affixed",
        ],
    ],
    'sink' => [
        'name' => "Sink",
        'use' => "You wash your hands for a solid minute. Good work.",
        'attributes' => [
            "affixed",
        ],
    ],
    'shower' => [
        'name' => "Shower",
        'use' => "You take an hour long shower since you have the time.",
        'attributes' => [
            "affixed",
        ],
    ],
    'bath' => [
        'name' => "Bath",
        'use' => "You spend several hours in a nice, warm bath.",
        'attributes' => [
            "affixed",
        ],
    ],
    'dinner-table' => [
        'name' => "Dinner Table",
        'attributes' => [
            "heavy",
        ],
    ],
    'dining-chair' => [
        'name' => "Dining Chair",
        'use' => [
            'location' => ["room"],
            'message' => "You have a bit of a sit down. Thrilling stuff.",
        ],
    ],
    'chair-ladder' => [
        'name' => "Chair Ladder",
        'attributes' => [
            "dangerous",
            "improvised",
        ],
    ],
    'bedside-locker' => [
        'name' => "Bedside Locker",
        'attributes' => [
            "container",
        ],
    ],
    'flashlight' => [
        'name' => "Flashlight",
        'states' => [
            'off' => "",
            'on'  => "On",
        ],
    ],
    'quarantine-barrier' => [
        'name' => "Quarantine Barrier",
        'states' => [
            'closed' => "",
            'open'   => "Open",
        ],
    ],
];
