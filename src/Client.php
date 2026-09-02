<?php

namespace BroadcastBuddy;

use BroadcastBuddy\Exceptions\AuthenticationException;
use BroadcastBuddy\Exceptions\BroadcastBuddyException;

class Client
{
    const BASE_URL = 'https://broadcastbuddy.app/api/v1';

    protected string $apiKey;
    protected int $timeout;

    /**
     * @param string|null $apiKey In Broadcast Buddy, your API Key is your Session ID.
     * @param int $timeout Request timeout in seconds.
     * @throws AuthenticationException
     */
    public function __construct(?string $apiKey = null, int $timeout = 30)
    {
        $this->apiKey = trim((string) ($apiKey ?? ''));
        $this->timeout = $timeout;

        if (empty($this->apiKey)) {
            throw new AuthenticationException('[BroadcastBuddy] API Key is required.');
        }
    }

    /**
     * Format a phone number into a valid WhatsApp JID (e.g. 233240001122@c.us).
     */
    public function formatChatId(string $phone): string
    {
        $clean = preg_replace('/[^0-9@.\-_\w]/', '', $phone);
        if (strpos($clean, '@') !== false) {
            return $clean;
        }
        $digits = preg_replace('/[^0-9]/', '', $clean);
        return "{$digits}@c.us";
    }

    /**
     * Internal HTTP requester utilizing cURL with stream fallback.
     */
    protected function request(string $path, string $method = 'POST', ?array $payload = null): array
    {
        $url = rtrim(self::BASE_URL, '/') . '/' . ltrim($path, '/');
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: BroadcastBuddy-PHP-SDK/1.0.1',
            'x-api-key: ' . $this->apiKey
        ];

        $jsonBody = $payload !== null ? json_encode($payload) : null;

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

            if ($jsonBody !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'Network error: ' . $curlError,
                    'status' => 'error'
                ];
            }
        } else {
            // Fallback for environments where cURL is disabled
            $options = [
                'http' => [
                    'method' => strtoupper($method),
                    'header' => implode("\r\n", $headers),
                    'content' => $jsonBody,
                    'timeout' => $this->timeout,
                    'ignore_errors' => true
                ]
            ];
            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'Stream error: Unable to connect to Broadcast Buddy API',
                    'status' => 'error'
                ];
            }
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response from server',
                'raw' => $response
            ];
        }

        if (!isset($data['success'])) {
            $data['success'] = !isset($data['status']) || $data['status'] !== 'error';
        }

        return $data;
    }

    /**
     * Send a standard text WhatsApp message.
     *
     * @param string $phone Recipient phone number (e.g. 233240001122).
     * @param string $message Text content.
     * @param array $options Optional mentions, quote message ID, link preview.
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        $chatId = $this->formatChatId($phone);
        $encodedKey = urlencode($this->apiKey);

        return $this->request("client/sendMessage/{$encodedKey}", 'POST', [
            'chatId' => $chatId,
            'contentType' => 'string',
            'content' => $message,
            'options' => $options
        ]);
    }

    /**
     * Send Base64 or remote URL media (image, video, document, audio).
     */
    public function sendMedia(string $phone, string $dataOrUrl, string $mimeType, string $filename = 'file', string $caption = ''): array
    {
        $chatId = $this->formatChatId($phone);
        $encodedKey = urlencode($this->apiKey);
        $cleanData = strpos($dataOrUrl, 'base64,') !== false ? explode('base64,', $dataOrUrl)[1] : $dataOrUrl;

        return $this->request("client/sendMessage/{$encodedKey}", 'POST', [
            'chatId' => $chatId,
            'contentType' => 'MessageMedia',
            'content' => [
                'mimetype' => $mimeType,
                'data' => $cleanData,
                'filename' => $filename
            ],
            'caption' => $caption
        ]);
    }

    /**
     * Send a JPG/PNG/WebP image from URL or Base64 data.
     */
    public function sendImage(string $phone, string $imageSource, string $caption = '', string $filename = 'image.png'): array
    {
        $mimeType = 'image/png';
        $lower = strtolower($imageSource);
        if (strpos($lower, 'data:image/jpeg') === 0 || substr($lower, -4) === '.jpg' || substr($lower, -5) === '.jpeg') {
            $mimeType = 'image/jpeg';
        } elseif (strpos($lower, 'data:image/webp') === 0 || substr($lower, -5) === '.webp') {
            $mimeType = 'image/webp';
        }

        return $this->sendMedia($phone, $imageSource, $mimeType, $filename, $caption);
    }

    /**
     * Send a PDF, Excel, Word, or zip document.
     */
    public function sendDocument(string $phone, string $docSource, string $filename = 'document.pdf', string $caption = ''): array
    {
        $mimeType = 'application/pdf';
        $lower = strtolower($filename);
        if (substr($lower, -5) === '.xlsx') {
            $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif (substr($lower, -5) === '.docx') {
            $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        } elseif (substr($lower, -4) === '.csv') {
            $mimeType = 'text/csv';
        } elseif (substr($lower, -4) === '.zip') {
            $mimeType = 'application/zip';
        }

        return $this->sendMedia($phone, $docSource, $mimeType, $filename, $caption);
    }

    /**
     * Send an audio voice note or sound file.
     */
    public function sendAudio(string $phone, string $audioSource, string $filename = 'audio.mp3'): array
    {
        return $this->sendMedia($phone, $audioSource, 'audio/mp3', $filename);
    }

    /**
     * Send an interactive WhatsApp multi-choice poll.
     */
    public function sendPoll(string $phone, string $question, array $options, bool $isMultiSelect = false): array
    {
        $chatId = $this->formatChatId($phone);
        $encodedKey = urlencode($this->apiKey);

        $pollOptions = array_map(function ($opt) {
            return ['name' => (string) $opt];
        }, $options);

        return $this->request("client/sendMessage/{$encodedKey}", 'POST', [
            'chatId' => $chatId,
            'contentType' => 'Poll',
            'content' => [
                'pollName' => $question,
                'pollOptions' => $pollOptions,
                'options' => [
                    'allowMultipleAnswers' => $isMultiSelect
                ]
            ]
        ]);
    }

    /**
     * Send a GPS Location / Map Pin.
     */
    public function sendLocation(string $phone, float $latitude, float $longitude, string $name = '', string $address = ''): array
    {
        $chatId = $this->formatChatId($phone);
        $encodedKey = urlencode($this->apiKey);

        return $this->request("client/sendMessage/{$encodedKey}", 'POST', [
            'chatId' => $chatId,
            'contentType' => 'Location',
            'content' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'name' => $name,
                'address' => $address
            ]
        ]);
    }

    /**
     * Send a Contact Card (vCard).
     */
    public function sendContact(string $phone, string $name, string $contactPhone, string $organization = ''): array
    {
        $chatId = $this->formatChatId($phone);
        $encodedKey = urlencode($this->apiKey);

        return $this->request("client/sendMessage/{$encodedKey}", 'POST', [
            'chatId' => $chatId,
            'contentType' => 'Contact',
            'content' => [
                'name' => $name,
                'phone' => $contactPhone,
                'organization' => $organization
            ]
        ]);
    }

    /**
     * Schedule a WhatsApp message for future dispatch.
     */
    public function scheduleMessage(string $phone, string $message, string $scheduledTime): array
    {
        $chatId = $this->formatChatId($phone);
        $encodedKey = urlencode($this->apiKey);

        return $this->request("schedule/{$encodedKey}", 'POST', [
            'chatId' => $chatId,
            'type' => 'text',
            'message' => $message,
            'scheduledTime' => $scheduledTime
        ]);
    }

    /**
     * Check connection state of the configured WhatsApp hotline session.
     */
    public function getStatus(): array
    {
        $encodedKey = urlencode($this->apiKey);
        $res = $this->request("session/status/{$encodedKey}", 'GET');

        $status = $res['status'] ?? null;
        $state = $res['state'] ?? null;
        $isConnected = in_array($status, ['CONNECTED', 'CONNECTED_STANDBY'], true) || $state === 'CONNECTED';

        return [
            'success' => $isConnected,
            'status' => $isConnected ? 'CONNECTED' : ($status ?? 'DISCONNECTED'),
            'user' => $res['user'] ?? null,
            'state' => $state,
            'raw' => $res
        ];
    }

    /**
     * Check if a phone number is registered on WhatsApp.
     */
    public function isOnWhatsApp(string $phone): array
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        $encodedKey = urlencode($this->apiKey);

        $res = $this->request("client/isRegisteredUser/{$encodedKey}", 'POST', [
            'number' => $digits
        ]);

        $isRegistered = false;
        if (isset($res['result'])) {
            $isRegistered = (bool) $res['result'];
        } elseif (isset($res['isRegistered'])) {
            $isRegistered = (bool) $res['isRegistered'];
        } elseif (isset($res['success'])) {
            $isRegistered = (bool) $res['success'];
        }

        return [
            'success' => $res['success'] ?? true,
            'exists' => $isRegistered,
            'isRegistered' => $isRegistered,
            'jid' => $isRegistered ? "{$digits}@c.us" : null,
            'phone' => $digits,
            'raw' => $res
        ];
    }

    /**
     * Alias for isOnWhatsApp(phone).
     */
    public function isRegisteredUser(string $phone): array
    {
        return $this->isOnWhatsApp($phone);
    }

    /**
     * Get the registered WhatsApp ID / JID for a phone number.
     */
    public function getNumberId(string $phone): array
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        $encodedKey = urlencode($this->apiKey);

        $res = $this->request("client/getNumberId/{$encodedKey}", 'POST', [
            'number' => $digits
        ]);

        $result = $res['result'] ?? null;
        $jid = null;

        if (is_array($result)) {
            $jid = $result['_serialized'] ?? $result['jid'] ?? null;
        } elseif (is_string($result)) {
            $jid = $result;
        }

        if (!$jid && isset($res['jid'])) {
            $jid = $res['jid'];
        }

        return [
            'success' => !empty($jid),
            'jid' => $jid ?? (!empty($res['success']) ? "{$digits}@c.us" : null),
            'user' => $digits,
            'raw' => $res
        ];
    }
}
