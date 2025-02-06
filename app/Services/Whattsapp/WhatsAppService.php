<?php

namespace App\Services\Whattsapp;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $apiUrl;
    protected $token;
    protected $phoneId;

    public function __construct()
    {
        $this->apiUrl = env('WHATSAPP_API_URL');
        $this->token = env('WHATSAPP_TOKEN');
        $this->phoneId = env('WHATSAPP_PHONE_ID');
    }

    public function sendMessage($phoneNumber, $message)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type'  => 'application/json',
        ])->post("{$this->apiUrl}/{$this->phoneId}/messages", [
            "messaging_product" => "whatsapp",
            "to"                => $phoneNumber,
            "type"              => "text",
            "text"              => [
                "body" => $message
            ],
        ]);

        return $response->json();
    }
}
