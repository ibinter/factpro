<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Les pages Inertia utilisent @vite — pas de manifest en environnement de test.
        $this->withoutVite();
    }
}
