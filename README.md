# BroadcastBuddy PHP SDK

This SDK allows developers to interact with the BroadcastBuddy WhatsApp API using a simple, single file PHP client.

It is designed to be lightweight, secure, and easy to integrate into any PHP project.

---

## Requirements

* PHP 7.4 or higher
* cURL extension enabled

No Composer. No external dependencies.

---

## Installation

### Step 1. Download the SDK

Download `BroadcastBuddy.php` and place it anywhere in your project.

Example:

```
/project-root/
│
├── BroadcastBuddy.php
└── index.php
```

---

### Step 2. Include the SDK

```php
require_once 'BroadcastBuddy.php';
```

---

## Initialization

Create a new instance using your **public API key**.

```php
$apiKey = 'your_api_key_here';
$bb = new BroadcastBuddy($apiKey);
```

---

## WhatsApp API Usage

### Start a WhatsApp Session

```php
$response = $bb->startSession();
print_r($response);
```

### Restart Session

```php
$response = $bb->restartSession();
print_r($response);
```

### Terminate Session

```php
$response = $bb->terminateSession();
print_r($response);
```

### Check Session Status

```php
$response = $bb->checkStatus();
print_r($response);
```

---

## Get Client Information

```php
$response = $bb->getClientInfo();
print_r($response);
```

---

## Get QR Code

```php
$response = $bb->getQrImage();
print_r($response);
```

---

## Send Messages

### Send Text Message

```php
$response = $bb->sendText(
    '233XXXXXXXX@c.us',
    'Hello from BroadcastBuddy'
);
print_r($response);
```

### Send Media Message

```php
$response = $bb->sendMedia(
    '233XXXXXXXX@c.us',
    $base64Data,
    'image/png',
    'image.png',
    'Optional caption'
);
print_r($response);
```

### Send Poll

```php
$response = $bb->sendPoll(
    '233XXXXXXXX@c.us',
    'Your favorite language?',
    ['PHP', 'JavaScript', 'Python'],
    false
);
print_r($response);
```

### Send Location

```php
$response = $bb->sendLocation(
    '233XXXXXXXX@c.us',
    5.6037,
    -0.1870,
    'Accra'
);
print_r($response);
```

---

## Fetch Chats and Contacts

### Get Chats

```php
$response = $bb->getChats();
print_r($response);
```

### Get Contacts

```php
$response = $bb->getContacts();
print_r($response);
```

---

## Error Handling

Wrap calls in a try catch block for safe error handling.

```php
try {
    $bb->sendText('233XXXXXXXX@c.us', 'Hello');
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

---

## API Base URL

All requests are sent to:

```
https://broadcastbuddy.app/api/v1
```

Authentication is handled automatically using your API key in the endpoint path.

---

## File Structure

```
/project-root/
├── BroadcastBuddy.php
└── index.php
```

---

## Security Notes

* Never expose your API key in frontend code
* Always store API keys in environment variables
* Rotate keys if compromised

---

## Support

For API documentation and updates, visit
[https://docs.broadcastbuddy.app](https://docs.broadcastbuddy.app)

For technical support, contact the BroadcastBuddy team.