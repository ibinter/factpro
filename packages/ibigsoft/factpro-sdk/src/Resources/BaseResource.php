<?php

namespace FactPro\Resources;

use FactPro\FactProClient;

abstract class BaseResource
{
    protected FactProClient $client;

    public function __construct(FactProClient $client)
    {
        $this->client = $client;
    }
}
