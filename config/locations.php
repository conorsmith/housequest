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
        'objects' => [
            "windbreaker",
            "overcoat",
            "scarf",
        ],
    ],
    'living-room' => [
        'title' => "Living Room",
        'description' => "The room in which you live, very literally nowadays.",
        'egresses' => [
            "hallway",
            "dining-room",
        ],
        'objects' => [
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
        'objects' => [
            "fridge",
            "freezer",
            "pantry",
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
    ],
    'downstairs-bathroom' => [
        'title' => "Downstairs Bathroom",
        'description' => "It's cosy, I guess.",
        'egresses' => [
            "utility-room",
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
        'objects' => [
            "step-ladder",
        ],
    ],
    'front-garden' => [
        'title' => "Front Garden",
        'description' => "This doesn't count... right?",
        'egresses' => [
            "the-street",
            "hallway",
            "garage",
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
            "other-bedroom",
        ],
    ],
    'master-bedroom' => [
        'title' => "Master Bedroom",
        'description' => "This is where the business happens.",
        'egresses' => [
            "en-suite",
            "landing",
        ],
        'objects' => [
            "hairdryer",
        ],
    ],
    'en-suite' => [
        'title' => "En Suite",
        'description' => "Le petit bathroom",
        'egresses' => [
            "master-bedroom",
        ],
    ],
    'other-bedroom' => [
        'title' => "Other Bedroom",
        'description' => "No toilet in here, folks.",
        'egresses' => [
            "landing",
        ],
    ],
    'box-room' => [
        'title' => "Box Room",
        'description' => "The short straw.",
        'egresses' => [
            "landing",
        ],
    ],
    'bathroom' => [
        'title' => "Bathroom",
        'description' => "Where everybody knows your name.",
        'egresses' => [
            "landing",
        ],
    ],
    'the-street' => [
        'title' => "The Street",
        'description' => "You step out from your front garden into the street and are immediately torn asunder in a hail of bullets. Quarantine is serious business, folks.",
        'egresses' => [],
    ],
    'attic' => [
        'title' => "Attic",
        'description' => "It's as good a time as any to sort this place out.",
        'egresses' => [
            "landing",
        ],
    ],
];
