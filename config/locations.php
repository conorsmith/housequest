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
            [
                'id' => "couch",
                'surface' => [
                    "wool-blanket",
                    "linen-blanket",
                    "couch-cushion" => 5,
                ],
            ],
            [
                'id' => "coffee-table",
                'surface' => [
                    "tv-remote",
                    "pack-of-crisps",
                ],
            ],
            "entertainment-unit",
            "television",
            "floor-lamp",
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
                    "passport",
                ],
                'surface' => [
                    "table-lamp",
                    "alarm-clock",
                ],
            ],
            [
                'id' => "bedside-locker",
                'contents' => [
                    //
                ],
                'surface' => [
                    "table-lamp",
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
            [
                'id' => "medicine-cabinet",
                'contents' => [
                    "sleeping-pills",
                    "toothpaste",
                    "toothbrush" => 2,
                    "dental-floss",
                    "disposable-razor" => 5,
                    "moisturiser" => 3,
                    "mouthwash",
                    "nail-clippers",
                ],
            ],
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
            [
                'id' => "medicine-cabinet",
                'contents' => [
                    "toothpaste",
                    "toothbrush" => 2,
                    "dental-floss",
                    "cotton-pad" => 46,
                    "moisturiser" => 2,
                ],
            ],
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
            "mysterious-sandwich",
            "water-tank",
            [
                'id' => "tattered-box",
                'contents' => [
                    "pager",
                ],
            ],
            [
                'id' => "cardboard-box",
                'contents' => [
                    'fur-coat',
                ],
            ],
            [
                'id' => "cardboard-box",
                'contents' => [
                    'christmas-lights' => 3,
                    'christmas-bauble' => 43,
                    'tinsel' => 6,
                    'christmas-tree-star',
                ],
            ],
            [
                'id' => "cardboard-box",
                'contents' => [
                ],
            ],
            [
                'id' => "storage-container",
                'contents' => [
                ],
            ],
            [
                'id' => "storage-container",
                'contents' => [
                ],
            ],
        ],
    ],
];
