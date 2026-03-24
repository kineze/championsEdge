<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    public function chat(array $messages): string
    {
        $response = Http::baseUrl((string) config('services.ollama.base_url'))
            ->connectTimeout((int) config('services.ollama.connect_timeout'))
            ->timeout((int) config('services.ollama.timeout'))
            ->post('/api/chat', [
                'model' => (string) config('services.ollama.chat_model'),
                'messages' => $messages,
                'stream' => false,
            ])
            ->throw()
            ->json();

        return (string) data_get($response, 'message.content', '');
    }
}
