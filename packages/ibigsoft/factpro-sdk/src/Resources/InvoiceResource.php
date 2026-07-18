<?php

namespace FactPro\Resources;

/**
 * Alias de DocumentResource filtré sur le type "invoice".
 */
class InvoiceResource extends DocumentResource
{
    public function list(array $params = []): array
    {
        return parent::list(array_merge(['type' => 'invoice'], $params));
    }
}
