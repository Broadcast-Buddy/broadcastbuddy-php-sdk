<?php

class BroadcastBuddy
{
    private string $baseUrl = 'https://broadcastbuddy.app/api/v1';
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        if (empty($apiKey)) {
            throw new InvalidArgumentException('API key is required');
        }

        $this->apiKey = $apiKey;
    }

    /* =========================
       Core HTTP Client
    ========================= */

    private function request(string $method, string $endpoint, $body = null)
    {
        $ch = curl_init($this->baseUrl . $endpoint);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: 0987'
            ]
        ];

        if ($method === 'POST' && $body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($status >= 400) {
            throw new Exception($response ?: 'Request failed');
        }

        return json_decode($response, true);
    }

    private function get(string $endpoint)
    {
        return $this->request('GET', $endpoint);
    }

    private function post(string $endpoint, $body = null)
    {
        return $this->request('POST', $endpoint, $body);
    }

    /* =========================
       WhatsApp API Methods
    ========================= */

    public function restartSession()
    {
        return $this->post("/session/restart/{$this->apiKey}");
    }

    public function terminateSession()
    {
        return $this->post("/session/terminate/{$this->apiKey}");
    }

    public function startSession()
    {
        return $this->post("/session/start/{$this->apiKey}");
    }

    public function checkStatus()
    {
        return $this->get("/session/status/{$this->apiKey}");
    }

    public function getClientInfo()
    {
        return $this->get("/client/getClassInfo/{$this->apiKey}");
    }

    public function getQrImage()
    {
        return $this->get("/session/qr/{$this->apiKey}/image");
    }

    public function sendMessage(array $payload)
    {
        return $this->post("/client/sendMessage/{$this->apiKey}", $payload);
    }

    public function getChats()
    {
        return $this->get("/client/getChats/{$this->apiKey}");
    }

    public function getContacts()
    {
        return $this->get("/client/getContacts/{$this->apiKey}");
    }

    /* =========================
       Helper Shortcuts
    ========================= */

    public function sendText(string $chatId, string $message)
    {
        return $this->sendMessage([
            'chatId' => $chatId,
            'contentType' => 'string',
            'content' => $message
        ]);
    }

    public function sendMedia(string $chatId, string $base64Data, string $mimeType, string $filename, string $caption = '')
    {
        return $this->sendMessage([
            'chatId' => $chatId,
            'contentType' => 'MessageMedia',
            'content' => [
                'mimetype' => $mimeType,
                'data' => $base64Data,
                'filename' => $filename
            ],
            'options' => [
                'caption' => $caption
            ]
        ]);
    }

    public function sendPoll(string $chatId, string $pollName, array $pollOptions, bool $allowMultipleAnswers = false)
    {
        return $this->sendMessage([
            'chatId' => $chatId,
            'contentType' => 'Poll',
            'content' => [
                'pollName' => $pollName,
                'pollOptions' => $pollOptions,
                'options' => [
                    'allowMultipleAnswers' => $allowMultipleAnswers
                ]
            ]
        ]);
    }

    public function sendLocation(string $chatId, float $latitude, float $longitude, string $description = '')
    {
        return $this->sendMessage([
            'chatId' => $chatId,
            'contentType' => 'Location',
            'content' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => $description
            ]
        ]);
    }
}
