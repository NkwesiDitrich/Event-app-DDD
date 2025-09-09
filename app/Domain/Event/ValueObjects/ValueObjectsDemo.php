<?php

namespace App\Domain\Event\ValueObjects;

/**
 * Demo class showing how to use the Event Value Objects
 * This demonstrates the business rules and validation in action
 */
class ValueObjectsDemo
{
    public static function demonstrateUsage(): array
    {
        $examples = [];

        try {
            // 1. EventTitle Examples
            $examples['EventTitle'] = [
                'valid' => [
                    new EventTitle('Annual Tech Conference 2025'),
                    new EventTitle('Workshop: Laravel Best Practices'),
                    new EventTitle('Community Meetup'),
                ],
                'invalid_examples' => [
                    'Empty title' => '', // Will throw exception
                    'Too long' => str_repeat('A', 151), // Will throw exception
                    'Too short' => 'Hi', // Will throw exception
                    'Only special chars' => '!@#$%', // Will throw exception
                ]
            ];

            // 2. EventDate Examples
            $examples['EventDate'] = [
                'valid' => [
                    new EventDate('2025-12-31'),
                    new EventDate('2025-10-15'),
                    new EventDate(date('Y-m-d', strtotime('+1 month'))),
                ],
                'invalid_examples' => [
                    'Past date' => '2023-01-01', // Will throw exception
                    'Invalid format' => '31-12-2025', // Will throw exception
                    'Too far future' => '2028-01-01', // Will throw exception
                ]
            ];

            // 3. EventLocation Examples
            $examples['EventLocation'] = [
                'valid' => [
                    new EventLocation('Lagos Convention Center'),
                    new EventLocation('Online via Zoom'),
                    new EventLocation('University of Lagos Auditorium'),
                ],
                'invalid_examples' => [
                    'Empty location' => '', // Will throw exception
                    'Too long' => str_repeat('Location ', 20), // Will throw exception
                    'Too short' => 'A', // Will throw exception
                ]
            ];

            // 4. EventType Examples
            $examples['EventType'] = [
                'valid' => [
                    EventType::feature(),
                    EventType::recent(),
                    new EventType('Feature'),
                ],
                'invalid_examples' => [
                    'Invalid type' => 'Premium', // Will throw exception
                    'Empty type' => '', // Will throw exception
                ]
            ];

            // 5. EventDescription Examples
            $examples['EventDescription'] = [
                'valid' => [
                    new EventDescription('Join us for an exciting tech conference featuring the latest innovations in software development.'),
                    new EventDescription('A hands-on workshop covering Laravel best practices and advanced techniques.'),
                ],
                'invalid_examples' => [
                    'Too short' => 'Short', // Will throw exception
                    'Too long' => str_repeat('Description ', 30), // Will throw exception
                    'Empty' => '', // Will throw exception
                ]
            ];

            // 6. EventTime Examples
            $examples['EventTime'] = [
                'valid' => [
                    new EventTime('14:30'),
                    new EventTime('2:30 PM'),
                    new EventTime('09:00:00'),
                    new EventTime(), // Empty time is allowed
                ],
                'invalid_examples' => [
                    'Invalid format' => '25:00', // Will throw exception
                    'Too long' => str_repeat('Time ', 30), // Will throw exception
                ]
            ];

        } catch (\Exception $e) {
            $examples['error'] = $e->getMessage();
        }

        return $examples;
    }

    public static function showBusinessRules(): array
    {
        return [
            'EventTitle' => [
                'Must be 3-150 characters',
                'Cannot be empty',
                'Must contain at least one alphanumeric character',
                'Cannot have excessive whitespace'
            ],
            'EventDate' => [
                'Must be in Y-m-d format',
                'Cannot be in the past',
                'Cannot be more than 2 years in the future',
                'Provides helper methods: isToday(), isTomorrow(), isUpcoming()'
            ],
            'EventLocation' => [
                'Must be 2-150 characters',
                'Cannot be empty',
                'Auto-capitalizes words',
                'Detects online vs physical events',
                'Cannot have excessive special characters'
            ],
            'EventType' => [
                'Must be either "Feature" or "Recent"',
                'Based on database enum constraints',
                'Provides helper methods: isFeatured(), isRecent()'
            ],
            'EventDescription' => [
                'Must be 10-255 characters',
                'Cannot be empty',
                'Must contain at least one letter',
                'Provides excerpt generation and keyword extraction'
            ],
            'EventTime' => [
                'Optional field (can be empty)',
                'Supports multiple time formats',
                'Converts between 12/24 hour formats',
                'Provides time-of-day detection (morning, afternoon, etc.)'
            ]
        ];
    }
}
