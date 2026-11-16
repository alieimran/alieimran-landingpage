<?php

/*
 | Starter templates offered when creating a checklist. Edit freely: key => title, description, items.
 | An item is a plain string, or [title, url, note]. These are generic starting points, not legal advice;
 | adjust to your own situation (for example, marriage requirements differ by state and religion).
 */
return [
    'templates' => [
        'work' => [
            'title' => 'Work checklist',
            'description' => 'Recurring things to keep work on track.',
            'items' => [
                "Review inbox and set today's top 3 priorities",
                'Update the project board / task list',
                'Back up important files',
                'Apply pending system and security updates',
                'Send the weekly progress report',
                'Review and renew certificates, licences and domains',
            ],
        ],
        'marriage' => [
            'title' => 'Marriage preparation',
            'description' => 'Plan ahead, step by step. Check the requirements of your state and religious office.',
            'items' => [
                'Agree on budget and wedding date',
                ['Attend the pre-marriage course', null, 'Book early; slots can fill up.'],
                ['Complete required health screening', null, 'Confirm what your state requires.'],
                ['Submit the marriage application at the religious / registry office', null, 'Check the documents list first.'],
                'Book the venue and caterer',
                'Book photographer / videographer',
                'Prepare attire and accessories',
                'Plan hantaran / mas kahwin and gifts',
                'Design and send invitations',
                'Arrange accommodation and transport for family',
                'Plan honeymoon and leave from work',
                'Update records after marriage (IC, bank, insurance, EPF nominees)',
            ],
        ],
        'trip' => [
            'title' => 'Trip / travel',
            'description' => 'Before you go.',
            'items' => [
                'Check passport validity (6+ months)',
                'Book flights and accommodation',
                'Arrange travel insurance',
                'Exchange currency / enable cards for overseas use',
                'Download maps and tickets for offline use',
                'Pack chargers, adapters and medication',
            ],
        ],
        'home' => [
            'title' => 'Home',
            'description' => 'Household to-dos.',
            'items' => [
                'Pay utility bills and internet',
                'Service the car / renew road tax and insurance',
                'Check smoke alarms and first-aid kit',
                'Declutter one room',
            ],
        ],
    ],
];
