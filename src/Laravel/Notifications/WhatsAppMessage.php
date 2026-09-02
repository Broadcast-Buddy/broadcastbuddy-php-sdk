<?php

namespace BroadcastBuddy\Laravel\Notifications;

class WhatsAppMessage
{
    public string $content = '';
    public ?string $recipient = null;
    public ?string $image = null;
    public ?string $document = null;
    public ?string $documentName = null;
    public array $pollOptions = [];
    public ?string $pollQuestion = null;

    public static function create(string $content = ''): self
    {
        $message = new self();
        $message->content = $content;
        return $message;
    }

    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function to(string $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function image(string $imageSource, string $caption = ''): self
    {
        $this->image = $imageSource;
        if (!empty($caption)) {
            $this->content = $caption;
        }
        return $this;
    }

    public function document(string $docSource, string $filename = 'document.pdf', string $caption = ''): self
    {
        $this->document = $docSource;
        $this->documentName = $filename;
        if (!empty($caption)) {
            $this->content = $caption;
        }
        return $this;
    }

    public function poll(string $question, array $options): self
    {
        $this->pollQuestion = $question;
        $this->pollOptions = $options;
        return $this;
    }
}
