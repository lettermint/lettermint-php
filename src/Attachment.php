<?php

declare(strict_types=1);

namespace Lettermint;

use Lettermint\Exceptions\LettermintValidationException;

/**
 * An email attachment for EmailBuilder::attach().
 *
 * ```php
 * new Attachment('invoice.pdf', $pdfBytes, 'application/pdf');   // raw bytes
 * Attachment::fromPath('/path/to/invoice.pdf');                   // a file
 * Attachment::fromBase64('logo.png', $base64, contentId: 'logo'); // already base64
 * ```
 *
 * The SDK base64-encodes the content. For a message array, use toArray().
 */
final class Attachment
{
    /** The content, base64-encoded. Set once: in the constructor, or by fromBase64(). */
    private string $base64;

    /**
     * @param  string  $filename  The file name the recipient sees.
     * @param  string  $content  The raw file content (bytes).
     * @param  string|null  $contentType  MIME type, for example `application/pdf`. Detected by the API when omitted.
     * @param  string|null  $contentId  Content-ID for inline images referenced as `cid:<contentId>` in the HTML.
     */
    public function __construct(
        public readonly string $filename,
        string $content,
        public readonly ?string $contentType = null,
        public readonly ?string $contentId = null,
    ) {
        if ($filename === '') {
            throw new LettermintValidationException('An attachment needs a filename.', 'attachments');
        }
        $this->base64 = base64_encode($content);
    }

    /**
     * An attachment from raw bytes.
     */
    public static function fromBytes(string $filename, string $content, ?string $contentType = null, ?string $contentId = null): self
    {
        return new self($filename, $content, $contentType, $contentId);
    }

    /**
     * An attachment whose content is already base64-encoded.
     */
    public static function fromBase64(string $filename, string $base64, ?string $contentType = null, ?string $contentId = null): self
    {
        $attachment = new self($filename, '', $contentType, $contentId);
        $attachment->base64 = $base64;

        return $attachment;
    }

    /**
     * An attachment read from a file. The file name defaults to the file's base name.
     */
    public static function fromPath(string $path, ?string $filename = null, ?string $contentType = null, ?string $contentId = null): self
    {
        $content = is_file($path) && is_readable($path) ? @file_get_contents($path) : false;
        if ($content === false) {
            throw new LettermintValidationException("The attachment file {$path} cannot be read.", 'attachments');
        }

        return new self($filename ?? basename($path), $content, $contentType, $contentId);
    }

    /**
     * The attachment in the API's format, with base64 content.
     *
     * @return array{filename: string, content: string, content_type?: string, content_id?: string}
     */
    public function toArray(): array
    {
        $attachment = ['filename' => $this->filename, 'content' => $this->base64];
        if ($this->contentType !== null) {
            $attachment['content_type'] = $this->contentType;
        }
        if ($this->contentId !== null) {
            $attachment['content_id'] = $this->contentId;
        }

        return $attachment;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'filename' => $this->filename,
            'contentType' => $this->contentType,
            'contentId' => $this->contentId,
            'size' => strlen((string) base64_decode($this->base64, false)),
        ];
    }
}
