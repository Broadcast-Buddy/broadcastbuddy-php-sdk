# Broadcast Buddy PHP Client & Laravel Service Provider 🚀

Official PHP SDK and Laravel Package for the **[Broadcast Buddy](https://broadcastbuddy.app)** WhatsApp Gateway API. Send formatted text, interactive multi-choice polls, high-resolution media, PDF invoices, GPS pins, and check real-time phone number registration on WhatsApp.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/broadcastbuddy/broadcastbuddy-php.svg)](https://packagist.org/packages/broadcastbuddy/broadcastbuddy-php)
[![Total Downloads](https://img.shields.io/packagist/dt/broadcastbuddy/broadcastbuddy-php.svg)](https://packagist.org/packages/broadcastbuddy/broadcastbuddy-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](https://opensource.org/licenses/MIT)

---

## 📦 Installation

Install via Composer:

```bash
composer require broadcastbuddy/broadcastbuddy-php
```

---

## 🚀 1. Vanilla PHP Usage

```php
require_once __DIR__ . '/vendor/autoload.php';

use BroadcastBuddy\Client;

// Initialize with your API Key (Session ID)
$bb = new Client('bb_live_YOUR_API_KEY');

// 1. Verify if a recipient is active on WhatsApp
$check = $bb->isOnWhatsApp('233240001122');
echo "On WhatsApp: " . ($check['isRegistered'] ? 'Yes' : 'No');

// 2. Send Formatted WhatsApp Text Message
$res = $bb->sendMessage('233240001122', '🚀 Hello from Broadcast Buddy PHP Client!');

// 3. Send Interactive Multi-Choice Poll
$bb->sendPoll('233240001122', 'Which feature should we build next?', [
    'AI Auto-Pilot Agent',
    'Custom Webhook Flows',
    'Enterprise CRM Sync'
]);

// 4. Send Image with Caption (URL or Base64)
$bb->sendImage('233240001122', 'https://example.com/receipt.png', 'Your Payment Receipt #1092');

// 5. Send PDF Document / Invoice
$bb->sendDocument('233240001122', 'data:application/pdf;base64,...', 'Invoice_2026.pdf', 'Monthly Invoice');

// 6. Check Live Hotline Connection Status
$status = $bb->getStatus();
echo "Hotline Status: " . $status['status'];
```

---

## ⚡ 2. Laravel Integration (8.x, 9.x, 10.x, 11.x)

The package supports **Laravel Auto-Discovery**. Once installed, add your API key to your `.env`:

```env
BROADCAST_BUDDY_API_KEY=bb_live_YOUR_API_KEY
BROADCAST_BUDDY_TIMEOUT=30
```

*(Optional) Publish the configuration file:*
```bash
php artisan vendor:publish --tag=broadcastbuddy-config
```

### Using the Laravel Facade:

```php
use BroadcastBuddy\Laravel\Facades\BroadcastBuddy;

class OrderController extends Controller
{
    public function complete(Order $order)
    {
        // Send WhatsApp notification
        BroadcastBuddy::sendMessage(
            $order->customer_phone,
            "🎉 Hi {$order->customer_name}, your order #{$order->id} is confirmed!"
        );

        // Send PDF Receipt
        BroadcastBuddy::sendDocument(
            $order->customer_phone,
            $order->receipt_url,
            "Receipt-{$order->id}.pdf",
            "Here is your official receipt"
        );
    }
}
```

### Using Dependency Injection:

```php
use BroadcastBuddy\Client as BroadcastBuddyClient;

public function sendAlert(BroadcastBuddyClient $bb)
{
    $bb->sendMessage('233240001122', 'System Alert 🚨');
}
```

---

## 🔔 3. Laravel Notification Channel

Send WhatsApp notifications using Laravel's standard Notification system:

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use BroadcastBuddy\Laravel\Notifications\WhatsAppChannel;
use BroadcastBuddy\Laravel\Notifications\WhatsAppMessage;

class OrderShippedNotification extends Notification
{
    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        return WhatsAppMessage::create("📦 Your package for Order #{$this->order->id} has shipped!")
            ->image("https://example.com/tracking.png");
    }
}
```

Then dispatch it normally:
```php
$user->notify(new OrderShippedNotification($order));
```

---

## 📚 API Reference

| Method | Arguments | Description |
| :--- | :--- | :--- |
| `sendMessage` | `string $phone, string $message, array $options = []` | Send formatted text message. |
| `sendPoll` | `string $phone, string $question, array $options, bool $isMultiSelect = false` | Send interactive multi-choice poll. |
| `sendImage` | `string $phone, string $imageSource, string $caption = '', string $filename = 'image.png'` | Send JPG/PNG/WebP image (URL or Base64). |
| `sendDocument`| `string $phone, string $docSource, string $filename = 'document.pdf', string $caption = ''` | Send PDF, Excel, Word, or zip document. |
| `sendAudio` | `string $phone, string $audioSource, string $filename = 'audio.mp3'` | Send voice notes. |
| `sendLocation` | `string $phone, float $lat, float $lng, string $name = '', string $address = ''` | Send a GPS Location / Map Pin. |
| `sendContact` | `string $phone, string $name, string $contactPhone, string $organization = ''` | Send a Contact Card (vCard). |
| `scheduleMessage`| `string $phone, string $message, string $scheduledTime` | Schedule message for future ISO timestamp. |
| `isOnWhatsApp` | `string $phone` | Verify if a number exists on WhatsApp. |
| `getNumberId` | `string $phone` | Fetch standard WhatsApp JID format. |
| `getStatus` | - | Check WhatsApp hotline connection state. |

---

## 📄 License

MIT © [Broadcast Buddy](https://broadcastbuddy.app)
