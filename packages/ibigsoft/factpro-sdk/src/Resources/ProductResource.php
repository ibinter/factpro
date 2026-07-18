<?php

namespace FactPro\Resources;

class ProductResource extends BaseResource
{
    public function list(array $params = []): array
    {
        return $this->client->request('GET', 'products', ['query' => $params]);
    }

    public function get(string $id): array
    {
        return $this->client->request('GET', "products/{$id}");
    }

    public function create(array $data): array
    {
        return $this->client->request('POST', 'products', ['json' => $data]);
    }

    public function update(string $id, array $data): array
    {
        return $this->client->request('PUT', "products/{$id}", ['json' => $data]);
    }

    public function delete(string $id): array
    {
        return $this->client->request('DELETE', "products/{$id}");
    }
}
