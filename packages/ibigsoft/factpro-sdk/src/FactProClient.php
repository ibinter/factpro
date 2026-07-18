<?php

namespace FactPro;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use FactPro\Exceptions\FactProException;
use FactPro\Exceptions\AuthException;
use FactPro\Exceptions\ValidationException;
use FactPro\Resources\DocumentResource;
use FactPro\Resources\CustomerResource;
use FactPro\Resources\ProductResource;
use FactPro\Resources\InvoiceResource;

class FactProClient
{
    protected Client $http;
    protected string $token;
    protected string $baseUrl;

    public function __construct(string $token, string $baseUrl = 'https://app.ibigfactpro.com')
    {
        $this->token = $token;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->http = new Client([
            'base_uri' => $this->baseUrl . '/api/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    public function documents(): DocumentResource
    {
        return new DocumentResource($this);
    }

    public function customers(): CustomerResource
    {
        return new CustomerResource($this);
    }

    public function products(): ProductResource
    {
        return new ProductResource($this);
    }

    public function invoices(): InvoiceResource
    {
        return new InvoiceResource($this);
    }

    public function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->http->request($method, $uri, $options);
            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (ClientException $e) {
            $status = $e->getResponse()->getStatusCode();
            $body = json_decode($e->getResponse()->getBody()->getContents(), true) ?? [];
            if ($status === 401) {
                throw new AuthException('Token invalide ou expiré.');
            }
            if ($status === 422) {
                throw new ValidationException($body['message'] ?? 'Erreur de validation.', $body['errors'] ?? []);
            }
            throw new FactProException($body['message'] ?? 'Erreur API.', $status);
        }
    }

    public function requestRaw(string $method, string $uri, array $options = []): string
    {
        try {
            $response = $this->http->request($method, $uri, $options);
            return $response->getBody()->getContents();
        } catch (ClientException $e) {
            $status = $e->getResponse()->getStatusCode();
            $body = json_decode($e->getResponse()->getBody()->getContents(), true) ?? [];
            if ($status === 401) {
                throw new AuthException('Token invalide ou expiré.');
            }
            throw new FactProException($body['message'] ?? 'Erreur API.', $status);
        }
    }
}
