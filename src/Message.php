<?php

declare(strict_types=1);

namespace Kvitrvn\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 Message implementation.
 * Common functionality to request & response.
 *
 * @see https://www.php-fig.org/psr/psr-7/#31-psrhttpmessagemessageinterface
 *
 * @author Benjamin Gaudé
 */
class Message implements MessageInterface
{
    private string $protocolVersion = '1.1';

    /**
     * @var array<string, string[]> ['original_header_name' => [...values], ...]
     */
    private array $headerOriginals = [];

    /**
     * @var array<string, string> [lowercase(original_header_name) => original_header_name, ...]
     */
    private array $headerRegistry = [];

    private ?StreamInterface $body;

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @return static
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        if ($version === $this->protocolVersion) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * @return string[]|string[][]
     */
    public function getHeaders(): array
    {
        return $this->headerOriginals;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headerRegistry[\strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')]);
    }

    /**
     * @return array<string>
     */
    public function getHeader(string $name): array
    {
        if (false === $this->hasHeader($name)) {
            return [];
        }

        $normalized = \strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');

        return $this->headerOriginals[$this->headerRegistry[$normalized]];
    }

    public function getHeaderLine(string $name): string
    {
        return \implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $value = $this->validateValue($value);
        $normalized = \strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');

        $new = clone $this;
        if (false === $this->hasHeader($name)) {
            unset($new->headerRegistry[$normalized]);
        }

        $new->headerRegistry[$normalized] = $name;
        $new->headerOriginals[$name] = $value;

        return $new;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        if ('' === $name) {
            throw new \InvalidArgumentException('Header name cannot be empty');
        }

        $new = clone $this;
        $new->setHeaders([$name => $value]);

        return $new;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        if (false === $this->hasHeader($name)) {
            return $this;
        }

        $normalized = \strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');

        $new = clone $this;
        unset($new->headerRegistry[$normalized], $new->headerOriginals[$name]);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        if (null === $this->body) {
            $this->body = new Stream();
        }

        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        if ($body === $this->body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    /**
     * @param array<string, string|string[]> $headers
     */
    private function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $value = $this->validateValue($value);
            $normalized = \strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
            if (false === $this->hasHeader($name)) {
                $this->headerRegistry[$normalized] = $name;
                $this->headerOriginals[$name] = $value;
            } else {
                $name = $this->headerRegistry[$name];
                $this->headerOriginals[$name] = \array_merge((array)$this->headerOriginals[$name], (array)$value);
            }
        }
    }

    /**
     * @param string|string[] $value
     * @return string[]
     */
    private function validateValue(string|array $value): array
    {
        if (false === is_array($value)) {
            if (1 !== \preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $value)) {
                throw new \InvalidArgumentException('Invalid value');
            }
            return [\trim($value, " \t")];
        }

        if (0 === \count($value)) {
            throw new \InvalidArgumentException('Invalid value. Value must not be a string or an array of strings, empty array given');
        }

        $r = [];
        foreach ($value as $v) {
            if (1 !== \preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@D", (string) $v)) {
                throw new \InvalidArgumentException('Invalid value');
            }

            $r[] = \trim($v, " \t");
        }

        return $r;
    }
}
