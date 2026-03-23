<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeylanGateway
{
    protected string $merchantId;
    protected string $password;
    protected string $baseUrl;
    protected string $apiVersion;

    public function __construct()
    {
        $this->merchantId = (string) config('seylan.merchant_id');
        $this->password = (string) config('seylan.password');
        $this->apiVersion = (string) config('seylan.api_version', '100');
        $this->baseUrl = config('seylan.sandbox')
            ? (string) config('seylan.base_test')
            : (string) config('seylan.base_live');
    }

    public function baseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    public function initiateCheckout(array $payload): array
    {
        $url = $this->baseUrl() . "/api/rest/version/{$this->apiVersion}/merchant/{$this->merchantId}/session";

        $response = Http::withBasicAuth(
            "merchant.{$this->merchantId}",
            $this->password
        )->post($url, $payload);

        if (!$response->successful()) {
            Log::error('Seylan INITIATE_CHECKOUT failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to initiate Seylan checkout.');
        }

        return $response->json();
    }

    public function retrieveOrder(string $orderId): array
    {
        $url = $this->baseUrl() . "/api/rest/version/{$this->apiVersion}/merchant/{$this->merchantId}/order/{$orderId}";

        $response = Http::withBasicAuth(
            "merchant.{$this->merchantId}",
            $this->password
        )->get($url);

        if (!$response->successful()) {
            Log::error('Failed to retrieve Seylan order', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to verify payment status.');
        }

        return $response->json();
    }
}
