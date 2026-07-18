<?php

namespace FactPro\Resources;

class CustomerResource extends BaseResource
{
    public function list(array $params = []): array
    {
        return $this->client->request('GET', 'customers', ['query' => $params]);
    }

    public function get(string $id): array
    {
        return $this->client->request('GET', "customers/{$id}");
    }

    public function create(array $data): array
    {
        return $this->client->request('POST', 'customers', ['json' => $data]);
    }

    public function update(string $id, array $data): array
    {
        return $this->client->request('PUT', "customers/{$id}", ['json' => $data]);
    }

    public function delete(string $id): array
    {
        return $this->client->request('DELETE', "customers/{$id}");
    }
}
