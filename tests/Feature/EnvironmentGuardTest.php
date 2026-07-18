<?php

/*
| Garde-fou : la suite de tests DOIT tourner sur sqlite :memory:
| (jamais sur la base MySQL de développement — RefreshDatabase l'effacerait).
*/

it('runs the test suite against an in-memory sqlite database', function () {
    expect(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(app()->environment())->toBe('testing');
});
