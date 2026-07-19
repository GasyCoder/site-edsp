<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class NewsletterCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly NewsletterCampaign $campaign,
        public readonly NewsletterSubscriber $subscriber,
        public readonly string $unsubscribeUrl,
    ) {}

    public function build(): self
    {
        $diskName = $this->campaign->attachment_disk ?: 'private';
        $attachmentExists = filled($this->campaign->attachment_path)
            && Storage::disk($diskName)->exists($this->campaign->attachment_path);
        $attachmentMimeType = $attachmentExists
            ? (Storage::disk($diskName)->mimeType($this->campaign->attachment_path) ?: 'application/octet-stream')
            : null;
        $isInlineImage = $attachmentExists && Str::startsWith((string) $attachmentMimeType, 'image/');
        $attachmentData = $isInlineImage
            ? Storage::disk($diskName)->get($this->campaign->attachment_path)
            : null;
        $attachmentSize = $attachmentExists
            ? Number::fileSize(Storage::disk($diskName)->size($this->campaign->attachment_path))
            : null;
        $externalHost = filled($this->campaign->external_url)
            ? parse_url($this->campaign->external_url, PHP_URL_HOST)
            : null;

        $mail = $this->subject($this->campaign->subject)
            ->view('emails.newsletter-campaign')
            ->with([
                'attachmentData' => $attachmentData,
                'attachmentExists' => $attachmentExists,
                'attachmentMimeType' => $attachmentMimeType,
                'attachmentSize' => $attachmentSize,
                'externalHost' => $externalHost,
                'isInlineImage' => $isInlineImage,
            ]);

        if ($attachmentExists && ! $isInlineImage) {
            $mail->attachFromStorageDisk(
                $diskName,
                $this->campaign->attachment_path,
                $this->campaign->attachment_name,
                ['mime' => $attachmentMimeType],
            );
        }

        return $mail;
    }
}
