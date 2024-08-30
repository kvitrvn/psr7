<?php

declare(strict_types=1);

namespace Kvitrvn\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 Stream implementation.
 *
 * @see https://www.php-fig.org/psr/psr-7/#34-psrhttpmessagestreaminterface
 *
 * @author Benjamin Gaudé
 */
class Stream implements StreamInterface
{
    private const READ_WRITE_HASH = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    /** @var resource|false */
    private $stream;
    private bool $seekable = false;
    private bool $readable = false;
    private bool $writable = false;
    private ?int $size = null;

    public function __construct(mixed $body)
    {
        if (true === \is_string($body)) {
            if (false !== $this->stream = \fopen('php://temp', 'r+')) {
                \fwrite($this->stream, $body);
                \fseek($this->stream, 0);
            }
        } elseif (true === \is_resource($body)) {
            $this->stream = $body;
        } else {
            throw new \InvalidArgumentException('Stream must be a resource');
        }

        $this->seekable = (bool) $this->getMetadata('seekable');
        $this->readable = isset(self::READ_WRITE_HASH['read'][$this->getMetadata('mode')]);
        $this->writable = isset(self::READ_WRITE_HASH['write'][$this->getMetadata('mode')]);
    }

    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->rewind();
        }

        return $this->getContents();
    }

    public function close(): void
    {
        if (false !== $this->stream) {
            \fclose($this->stream);
            $this->detach();
        }
    }

    public function detach()
    {
        if (false === $this->stream) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        if (null !== $this->size) {
            return $this->size;
        }

        if (false === $this->stream) {
            return null;
        }

        $stats = \fstat($this->stream);
        if (true === isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
    }

    public function tell(): int
    {
        if (null === $this->stream || false === $this->stream) {
            throw new \RuntimeException('Stream is detached');
        }

        $result = \ftell($this->stream);
        if (false === $result) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function eof(): bool
    {
        if (null === $this->stream || false === $this->stream) {
            throw new \RuntimeException('Stream is detached');
        }

        return \feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek(int $offset, int $whence = \SEEK_SET): void
    {
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable.');
        }

        if (false !== $this->stream && -1 === \fseek($this->stream, $offset, $whence)) {
            throw new \RuntimeException('Unable to seek in stream.');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write(string $string): int
    {
        if (null === $this->stream || false === $this->stream) {
            throw new \RuntimeException('Stream is detached');
        }

        if (false === $this->writable) {
            throw new \RuntimeException('Stream is not writable.');
        }

        $result = \fwrite($this->stream, $string);

        if (false === $result) {
            throw new \RuntimeException('Unable to write to stream.');
        }

        $this->size = null;

        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * @param int<1, max> $length
     */
    public function read(int $length): string
    {
        if (null === $this->stream || false === $this->stream) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new \RuntimeException('Stream is not readable.');
        }

        $result = \fread($this->stream, $length);

        if (false === $result) {
            throw new \RuntimeException('Unable to read from stream.');
        }

        return $result;
    }

    public function getContents(): string
    {
        if (null === $this->stream || false === $this->stream) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new \RuntimeException('Stream is not readable.');
        }

        $contents = \stream_get_contents($this->stream);

        if (false === $contents) {
            throw new \RuntimeException('Unable to read stream contents.');
        }

        return $contents;
    }

    public function getMetadata(?string $key = null)
    {
        if (null === $this->stream || false === $this->stream) {
            return null === $key ? [] : null;
        }

        $meta = \stream_get_meta_data($this->stream);

        if (null === $key) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }
}
