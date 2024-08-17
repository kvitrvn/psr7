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
        // TODO: Implement getRequestTarget() method.
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        // TODO: Implement withRequestTarget() method.
    }

    public function getMethod(): string
    {
        // TODO: Implement getMethod() method.
    }

    public function withMethod(string $method): RequestInterface
    {
        // TODO: Implement withMethod() method.
    }

    public function getUri(): UriInterface
    {
        // TODO: Implement getUri() method.
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        // TODO: Implement withUri() method.
    }
}
