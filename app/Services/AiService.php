<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private string $provider;
    private string $model;
    private string $apiKey;

    // Available models per provider
    public const MODELS = [
        'anthropic' => [
            'claude-haiku-4-5'  => 'Claude Haiku 4.5  (fast, cheap)',
            'claude-sonnet-4-5' => 'Claude Sonnet 4.5  (smarter)',
        ],
        'groq' => [
            'meta-llama/llama-4-maverick-17b-128e-instruct' => 'Llama 4 Maverick  (vision, fast)',
            'llama-3.3-70b-versatile'                       => 'Llama 3.3 70B  (text, powerful)',
            'llama-3.1-8b-instant'                          => 'Llama 3.1 8B  (text, fastest)',
        ],
    ];

    public function __construct()
    {
        $this->provider = AppSetting::get('ai_provider', 'anthropic');
        $this->model    = AppSetting::get('ai_model', 'claude-haiku-4-5');

        // Each provider stores its own encrypted key independently
        $settingKey   = match ($this->provider) {
            'groq'  => 'ai_groq_key',
            default => 'ai_anthropic_key',
        };
        $encryptedKey = AppSetting::get($settingKey, '');

        try {
            $this->apiKey = $encryptedKey
                ? Crypt::decryptString($encryptedKey)
                : config('services.anthropic.key', '');  // .env fallback for Anthropic
        } catch (\Throwable) {
            $this->apiKey = config('services.anthropic.key', '');
        }
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Send a chat message (with optional image) and return the model's response text.
     *
     * @throws \RuntimeException on API failure
     */
    public function chat(string $text, ?string $imageBase64 = null, ?string $mimeType = null): string
    {
        return match ($this->provider) {
            'groq'  => $this->chatGroq($text, $imageBase64, $mimeType),
            default => $this->chatAnthropic($text, $imageBase64, $mimeType),
        };
    }

    public function provider(): string { return $this->provider; }
    public function model(): string    { return $this->model; }
    public function hasKey(): bool     { return $this->apiKey !== ''; }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function chatAnthropic(string $text, ?string $imageBase64, ?string $mimeType): string
    {
        $content = [];

        if ($imageBase64 && $mimeType) {
            $content[] = [
                'type'   => 'image',
                'source' => [
                    'type'       => 'base64',
                    'media_type' => $mimeType,
                    'data'       => $imageBase64,
                ],
            ];
        }

        $content[] = ['type' => 'text', 'text' => $text];

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => 512,
            'messages'   => [['role' => 'user', 'content' => $content]],
        ]);

        if ($response->successful()) {
            return $response->json('content.0.text', '');
        }

        Log::warning('AiService Anthropic error', ['status' => $response->status(), 'body' => $response->body()]);
        throw new \RuntimeException('Anthropic API error (' . $response->status() . ')');
    }

    private function chatGroq(string $text, ?string $imageBase64, ?string $mimeType): string
    {
        // Groq supports vision via the OpenAI image_url format for vision-capable models
        $userContent = ($imageBase64 && $mimeType)
            ? [
                ['type' => 'text',      'text'      => $text],
                ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$imageBase64}"]],
              ]
            : $text;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'      => $this->model,
            'max_tokens' => 512,
            'messages'   => [['role' => 'user', 'content' => $userContent]],
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content', '');
        }

        Log::warning('AiService Groq error', ['status' => $response->status(), 'body' => $response->body()]);
        throw new \RuntimeException('Groq API error (' . $response->status() . ')');
    }
}
