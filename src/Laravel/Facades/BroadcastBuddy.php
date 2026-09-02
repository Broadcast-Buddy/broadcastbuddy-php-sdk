<?php

namespace BroadcastBuddy\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendMessage(string $phone, string $message, array $options = [])
 * @method static array sendPoll(string $phone, string $question, array $options, bool $isMultiSelect = false)
 * @method static array sendMedia(string $phone, string $dataOrUrl, string $mimeType, string $filename = 'file', string $caption = '')
 * @method static array sendImage(string $phone, string $imageSource, string $caption = '', string $filename = 'image.png')
 * @method static array sendDocument(string $phone, string $docSource, string $filename = 'document.pdf', string $caption = '')
 * @method static array sendAudio(string $phone, string $audioSource, string $filename = 'audio.mp3')
 * @method static array sendLocation(string $phone, float $latitude, float $longitude, string $name = '', string $address = '')
 * @method static array sendContact(string $phone, string $name, string $contactPhone, string $organization = '')
 * @method static array scheduleMessage(string $phone, string $message, string $scheduledTime)
 * @method static array getStatus()
 * @method static array isOnWhatsApp(string $phone)
 * @method static array getNumberId(string $phone)
 *
 * @see \BroadcastBuddy\Client
 */
class BroadcastBuddy extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'broadcastbuddy';
    }
}
