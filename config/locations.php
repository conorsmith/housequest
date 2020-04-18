<?php
declare(strict_types=1);

return [
    'hallway' => [
        'title' => "Hallway",
        'description' => "The outside world is so close, but you know you cannot enter it during quarantine. Oh well.",
        'egresses' => [
            "front-garden",
            "living-room",
            "staircase",
            "kitchen",
        ],
        'items' => [
            "windbreaker",
            "overcoat",
            "scarf",
            "telephone",
        ],
    ],
    'living-room' => [
        'title' => "Living Room",
        'description' => "The room in which you live, very literally nowadays.",
        'egresses' => [
            "hallway",
            "dining-room",
        ],
        'items' => [
            "couch",
            "wool-blanket",
            "linen-blanket",
            "tv-remote",
            "digital-remote",
            "entertainment-unit",
            "television",
        ],
    ],
    'staircase' => [
        'title' => "Staircase",
        'description' => "Let's get vertical, baby!",
        'egresses' => [
            "hallway",
            "landing",
        ],
    ],
    'kitchen' => [
        'title' => "Kitchen",
        'description' => "Let's getting cooking, baby!",
        'egresses' => [
            "hallway",
            "dining-room",
            "utility-room",
        ],
        'items' => [
            [
                'id' => "fridge",
                'contents' => [
                    "red-pepper",
                    "yellow-pepper" => 3,
                    "cheddar-cheese" => 2,
                    "milk" => 2,
                ],
            ],
            [
                'id' => "freezer",
                'contents' => [
                    "phish-food",
                    "peanut-butter-cup",
                ],
            ],
            [
                'id' => "pantry",
                'contents' => [
                    "coco-pops",
                ],
            ],
            "slice-of-bread" => 16,
            "pack-of-crisps" => 6,
        ],
    ],
    'dining-room' => [
        'title' => "Dining Room",
        'description' => "Oh sit down.",
        'egresses' => [
            "living-room",
            "kitchen",
            "back-garden",
        ],
        'items' => [
            "dinner-table",
            "dining-chair" => 4,
        ],
    ],
    'utility-room' => [
        'title' => "Utility Room",
        'description' => "It's steamy in here",
        'egresses' => [
            "garage",
            "kitchen",
            "downstairs-bathroom",
            "back-garden",
        ],
        'items' => [
            "flashlight",
        ],
    ],
    'downstairs-bathroom' => [
        'title' => "Downstairs Bathroom",
        'description' => "It's cosy, I guess.",
        'egresses' => [
            "utility-room",
        ],
        'items' => [
            "toilet",
            "sink",
        ],
    ],
    'garage' => [
        'title' => "Garage",
        'description' => "Vroom vroom!",
        'egresses' => [
            "front-garden",
            "utility-room",
        ],
    ],
    'back-garden' => [
        'title' => "Back Garden",
        'description' => "The great outdoors!",
        'egresses' => [
            "front-garden",
            "dining-room",
            "utility-room",
            "shed",
        ],
    ],
    'shed' => [
        'title' => "Shed",
        'description' => "It's man time!",
        'egresses' => [
            "back-garden",
        ],
        'items' => [
            "step-ladder",
        ],
    ],
    'front-garden' => [
        'title' => "Front Garden",
        'description' => "This doesn't count... right?",
        'egresses' => [
            "hallway",
            "garage",
            "back-garden",
        ],
        'items' => [
            [
                'id' => "letter-box",
                'contents' => [
                    "quarantine-extension-notice",
                ],
            ],
            "quarantine-barrier",
        ],
    ],
    'landing' => [
        'title' => "Landing",
        'description' => "Now we're cooking with upstairs!",
        'egresses' => [
            "master-bedroom",
            "box-room",
            "staircase",
            "bathroom",
            "guest-bedroom",
        ],
    ],
    'master-bedroom' => [
        'title' => "Master Bedroom",
        'description' => "This is where the business happens.",
        'egresses' => [
            "en-suite",
            "landing",
        ],
        'items' => [
            "hairdryer",
            "bed",
            [
                'id' => "bedside-locker",
                'contents' => [
                    "cheddar-cheese-sandwich" => 2,
                    "red-pepper",
                ],
            ],
            [
                'id' => "bedside-locker",
                'contents' => [
                    "yellow-pepper",
                ],
            ],
        ],
    ],
    'en-suite' => [
        'title' => "En Suite",
        'description' => "Le petit bathroom",
        'egresses' => [
            "master-bedroom",
        ],
        'items' => [
            "toilet",
            "sink",
            "shower",
        ],
    ],
    'guest-bedroom' => [
        'title' => "Guest Bedroom",
        'description' => "No toilet in here, folks.",
        'egresses' => [
            "landing",
        ],
        'items' => [
            "bed",
        ],
    ],
    'box-room' => [
        'title' => "Box Room",
        'description' => "The short straw.",
        'egresses' => [
            "landing",
        ],
        'items' => [
            "bed",
        ],
    ],
    'bathroom' => [
        'title' => "Bathroom",
        'description' => "Where everybody knows your name.",
        'egresses' => [
            "landing",
        ],
        'items' => [
            "toilet",
            "sink",
            "bath",
        ],
    ],
    'the-street' => [
        'title' => "The Street",
        'description' => "You step out from your front garden into the street and are immediately torn asunder in a hail of bullets. Quarantine is serious business, folks.",
        'egresses' => [
            'front-garden',
            'the-next-street-over',
        ],
    ],
    'the-next-street-over' => [
        'title' => "The Next Street Over",
        'description' => "",
        'egresses' => [],
    ],
    'attic' => [
        'title' => "Attic",
        'description' => "It's as good a time as any to sort this place out.",
        'egresses' => [
            "landing",
        ],
        'items' => [
            "covid-19-cure",
        ],
    ],
];
