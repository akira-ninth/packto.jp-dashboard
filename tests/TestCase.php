<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Phase 13j: テスト中は SMTP を叩かない (Mail::fake で常に in-memory)
        Mail::fake();
    }
}
