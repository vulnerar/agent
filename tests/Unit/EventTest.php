<?php

use Vulnerar\Agent\Event;

it('can store event data', function () {
    $event = new Event(
        'test',
        [
            'foo' => 'bar',
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'login' => 'user@example.com',
            ],
            'ip_address' => '127.0.0.1',
        ]
    );

    expect($event->type)->toBe('test')
        ->and($event->data)->toBe([
            'foo' => 'bar',
        ])
        ->and($event->user)->toBe([
            'id' => 1,
            'name' => 'John Doe',
            'login' => 'user@example.com',
        ])
        ->and($event->ipAddress)->toBe('127.0.0.1')
        ->and($event->timestamp)->toBeFloat();

    $event = new Event('test', []);

    expect($event->type)->toBe('test')
        ->and($event->data)->toBe([])
        ->and($event->user)->toBeNull()
        ->and($event->ipAddress)->toBeNull()
        ->and($event->timestamp)->toBeFloat();
});

it('can transform to array', function () {
    $event = new Event(
        'test',
        [
            'foo' => 'bar',
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'login' => 'user@example.com',
            ],
            'ip_address' => '127.0.0.1',
        ]
    );

    expect($event->toArray())->toBe([
        'type' => 'test',
        'data' => [
            'foo' => 'bar',
        ],
        'user' => [
            'id' => 1,
            'name' => 'John Doe',
            'login' => 'user@example.com',
        ],
        'ip_address' => '127.0.0.1',
        'timestamp' => $event->timestamp,
    ]);
});