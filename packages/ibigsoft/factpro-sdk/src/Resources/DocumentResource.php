<?php

namespace FactPro\Resources;

class DocumentResource extends BaseResource
{
    public function list(array $params = []): array
    {
        return $this->client->request('GET', 'documents', ['query' => $params]);
    }

    public function get(string $id): array
    {
        return $this->client->request('GET', "documents/{$id}");
    }

    public function create(array $data): array
    {
        return $this->client->request('POST', 'documents', ['json' => $data]);
    }

    public function update(string $id, array $data): array
    {
        return $this->client->request('PUT', "documents/{$id}", ['json' => $data]);
    }

    public function delete(string $id): array
    {
        return $this->client->request('DELETE', "documents/{$id}");
    }

    public function finalize(string $id): array
    {
        return $this->client->request('POST', "documents/{$id}/finalize");
    }

    public function pdf(string $id): string
    {
        return $this->client->requestRaw('GET', "documents/{$id}/pdf");
    }
}
