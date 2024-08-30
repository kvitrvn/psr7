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
    public function getRequestTarget(): string
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getMethod(): string
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function withMethod(string $method): RequestInterface
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getUri(): UriInterface
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
