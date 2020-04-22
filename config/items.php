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
            "ingestible",
        ],
    ],
    'yellow-pepper' => [
        'name' => "Yellow Pepper",
        'attributes' => [
            "ingestible",
        ],
    ],
    'phish-food' => [
        'name' => "Ben & Jerry's Phish Food",
        'attributes' => [
            "ingestible",
        ],
    ],
    'peanut-butter-cup' => [
        'name' => "Ben & Jerry's Peanut Butter Cup",
        'attributes' => [
            "ingestible",
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
            "ingestible",
        ],
    ],
    'pack-of-crisps' => [
        'name' => "Pack of Crisps",
        'attributes' => [
            "ingestible",
        ],
    ],
    'crisp-half-sandwich' => [
        'name' => "Crisp Half-Sandwich",
        'attributes' => [
            "ingestible",
        ],
    ],
    'crisp-sandwich' => [
        'name' => "Crisp Sandwich",
        'attributes' => [
            "ingestible",
        ],
    ],
    'cheddar-cheese' => [
        'name' => "Cheddar Cheese",
        'portions' => 4,
        'attributes' => [
            "ingestible",
        ],
    ],
    'cheddar-cheese-half-sandwich' => [
        'name' => "Cheddar Cheese Half-Sandwich",
        'attributes' => [
            "ingestible",
        ],
    ],
    'cheddar-cheese-sandwich' => [
        'name' => "Cheddar Cheese Sandwich",
        'attributes' => [
            "ingestible",
        ],
    ],
    'milk' => [
        'name' => "Milk",
        'portions' => 10,
        'attributes' => [
            "ingestible",
        ],
    ],
    'coco-pops' => [
        'name' => "Coco Pops",
        'portions' => 12,
        'attributes' => [
            "ingestible",
        ],
    ],
    'bowl-of-coco-pops' => [
        'name' => "Bowl of Coco Pops",
        'attributes' => [
            "ingestible",
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
        'description' => "It's a syringe filled with an amber liquid. It has a label reading \"Covid-19 Cure\" written with a thick black marker.",
    ],
    'telephone' => [
        'name' => "Telephone",
    ],
    'dented-letter-box' => [
        'name' => "Severely Dented Letter Box",
        'description' => "A letter box bearing the damage from a severe bat-beating. Good luck getting that open.",
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
    'passport' => [
        'name' => "Passport",
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
        'description' => "It's the barrier that was erected outside your home and everybody else's home when this whole thing started. There's a gate built in for essential passage.",
        'attributes' => [
            "affixed",
        ],
        'states' => [
            'closed' => "",
            'open'   => "Open",
        ],
    ],
    'tattered-box' => [
        'name' => "Tattered Box",
        'description' => "It's a damaged cardboard box with the words \"Cyrpian Aebersold\" written on the side. It probably belonged to the former residents of the house, as you do not recognise it.",
        'attributes' => [
            "container",
        ],
    ],
    'pager' => [
        'name' => "Pager",
        'description' => "It's an old pager adorned with ALF branding. It's switched off.",
        'states' => [
            'off' => "",
            'on'  => [
                'label' => "On",
                'description' => "It's an old pager adorned with ALF branding. It's switched on.",
            ],
        ],
    ],
    'cardboard-box' => [
        'name' => "Cardboard Box",
        'attributes' => [
            "container",
        ],
    ],
    'christmas-lights' => [
        'name' => "Christmas Lights",
    ],
    'christmas-bauble' => [
        'name' => "Christmas Bauble",
    ],
    'tinsel' => [
        'name' => "Tinsel",
    ],
    'christmas-tree-star' => [
        'name' => "Christmas Tree Star",
    ],
    'storage-container' => [
        'name' => "Storage Container",
        'attributes' => [
            "container",
        ],
    ],
    'water-tank' => [
        'name' => "Water Tank",
    ],
    'fur-coat' => [
        'name' => "Fur Coat",
    ],
    'mysterious-sandwich' => [
        'name' => "Mysterious Sandwich",
        'description' => "It's a sandwich. The bread appears to be bread, basically. The filling is an indiscernible spongy grey paste.",
        'attributes' => [
            "ingestible",
            "toxic",
        ],
    ],
    'medicine-cabinet' => [
        'name' => "Medicine Cabinet",
        'attributes' => [
            "container",
        ],
    ],
    'sleeping-pills' => [
        'name' => "Sleeping Pills",
        'portions' => 12,
        'attributes' => [
            "ingestible",
        ],
    ],
    'toothpaste' => [
        'name' => "Toothpaste",
        'portions' => 30,
        'attributes' => [
            "ingestible",
        ],
    ],
    'toothbrush' => [
        'name' => "Toothbrush",
    ],
    'disposable-razor' => [
        'name' => "Disposable Razor",
    ],
    'dental-floss' => [
        'name' => "Dental Floss",
        'portions' => 50,
    ],
    'mouthwash' => [
        'name' => "Mouthwash",
        'portions' => 25,
        'attributes' => [
            "ingestible",
        ],
    ],
    'moisturiser' => [
        'name' => "Moisturiser",
        'portions' => 30,
        'attributes' => [
            "ingestible",
        ],
    ],
    'cotton-pad' => [
        'name' => "Cotton Pad",
    ],
    'nail-clippers' => [
        'name' => "Nail Clippers",
    ],
    'table-lamp' => [
        'name' => "Table Lamp",
        'states' => [
            'off' => "",
            'on'  => "On",
        ],
    ],
    'alarm-clock' => [
        'name' => "Alarm Clock",
    ],
    'coffee-table' => [
        'name' => "Coffee Table",
    ],
];
