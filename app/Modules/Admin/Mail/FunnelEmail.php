<?php

declare(strict_types=1);

namespace App\Modules\Admin\Mail;

use App\Modules\Admin\Models\FunnelEmailLog;
use App\Modules\Admin\Models\FunnelStep;
use App\Modules\Admin\Services\FunnelService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FunnelEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $processedHtml;
    public string $processedText;

    public function __construct(
        public FunnelEmailLog $emailLog,
        public FunnelStep $step
    ) {
        $service = app(FunnelService::class);

        // Process placeholders in content
        $this->processedHtml = $this->injectTracking(
            $service->processPlaceholders($step->body_html, $emailLog->user)
        );

        $this->processedText = $service->processPlaceholders(
            $step->body_text ?? strip_tags($step->body_html),
            $emailLog->user
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->step->from_email, $this->step->from_name),
            subject: $this->emailLog->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin::mail.funnel-email',
            text: 'admin::mail.funnel-email-text',
            with: [
                'htmlContent' => $this->processedHtml,
                'textContent' => $this->processedText,
            ],
        );
    }

    /**
     * Inject tracking pixel and wrap links for click tracking.
     */
    protected function injectTracking(string $html): string
    {
        // Wrap links for click tracking
        $html = $this->wrapLinksForTracking($html);

        // Inject open tracking pixel at the end
        $trackingPixel = $this->getTrackingPixel();
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $trackingPixel . '</body>', $html);
        } else {
            $html .= $trackingPixel;
        }

        return $html;
    }

    /**
     * Get the tracking pixel HTML.
     */
    protected function getTrackingPixel(): string
    {
        $trackingUrl = route('backoffice.funnel.track.open', ['uuid' => $this->emailLog->uuid]);

        return sprintf(
            '<img src="%s" width="1" height="1" alt="" style="display:none;border:0;width:1px;height:1px;" />',
            $trackingUrl
        );
    }

    /**
     * Wrap all links for click tracking.
     */
    protected function wrapLinksForTracking(string $html): string
    {
        // Pattern to match href attributes
        $pattern = '/href=["\']([^"\']+)["\'](?![^>]*data-no-track)/i';

        return preg_replace_callback($pattern, function ($matches) {
            $originalUrl = $matches[1];

            // Skip tracking for certain URLs
            if ($this->shouldSkipTracking($originalUrl)) {
                return $matches[0];
            }

            // Encode the original URL
            $linkId = base64_encode($originalUrl);
            $trackingUrl = route('backoffice.funnel.track.click', [
                'uuid' => $this->emailLog->uuid,
                'linkId' => $linkId,
            ]);

            return 'href="' . $trackingUrl . '"';
        }, $html);
    }

    /**
     * Determine if a URL should skip click tracking.
     */
    protected function shouldSkipTracking(string $url): bool
    {
        // Skip mailto links
        if (str_starts_with($url, 'mailto:')) {
            return true;
        }

        // Skip tel links
        if (str_starts_with($url, 'tel:')) {
            return true;
        }

        // Skip anchors
        if (str_starts_with($url, '#')) {
            return true;
        }

        // Skip unsubscribe links (you might want to handle these differently)
        if (str_contains($url, 'unsubscribe')) {
            return true;
        }

        return false;
    }
}
