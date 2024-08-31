<?php

declare(strict_types=1);

namespace Kvitrvn\Psr7;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * PSR-7 Request implementation.
 *
 * @see https://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface
 *
 * @author Benjamin Gaudé
 */
class Request extends Message implements RequestInterface
{
    private string $requestTarget = '';

    /**
     * @param array<string, string[]> $headers
     */
    public function __construct(
        private string $method,
        private UriInterface $uri,
        array $headers = [],
        string $protocolVersion = '1.1'
    ) {
        $this->assertMethod($method);
        $this->setHeaders($headers);
        $this->protocolVersion = $protocolVersion;
    }

    public function getRequestTarget(): string
    {
        if ('' !== $this->requestTarget) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ('' == $target) {
            $target = '/';
        }

        if ('' != $this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        if (1 == \preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): RequestInterface
    {
        $this->assertMethod($method);

        $new = clone $this;
        $new->method = \strtoupper($method);

        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (false === $preserveHost || false === $this->hasHeader('Host')) {
            $new->updateHost();
        }

        return $new;
    }

    private function updateHost(): void
    {
        $host = $this->uri->getHost();

        if ('' == $host) {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        $headerHost = $this->getHeader('host');
        if (true === empty($headerHost)) {
            $this->headerRegistry['host'] = 'Host';
        }

        $this->headerOriginals = ['Host' => [$host]] + $this->headerOriginals;
    }

    private function assertMethod(string $method): void
    {
        if ('' === $method) {
            throw new \InvalidArgumentException('Method must be a non-empty string.');
        }
    }
}
