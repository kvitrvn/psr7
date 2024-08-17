<?php

declare(strict_types=1);

namespace Kvitrvn\Psr7;

use Psr\Http\Message\UriInterface;

/**
 * PSR-7 URI implementation.
 *
 * @author Benjamin Gaudé
 */
class Uri implements UriInterface
{
    private const STANDARD_PORT = [
        'http' => 80,
        'https' => 443,
    ];
    private const REG_PATH = '/[^a-zA-Z0-9_\-.~!$&\'()*+,;=%:@\/]++|%(?![A-Fa-f0-9]{2})/';
    private const REG_QUERY_FRAGMENT = '/[^a-zA-Z0-9_\-.~!$&\'()*+,;=%:@\/]++|%(?![A-Fa-f0-9]{2})/';
    private const REG_USER_INFO = '/[a-zA-Z0-9_\-.~!$&\'()*+,;=]++/';

    private string $scheme = '';
    private string $userInfo = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        if ('' === $uri) {
            return;
        }

        $parts = \parse_url($uri);
        if (false === $parts) {
            throw new \InvalidArgumentException("Unable to parse URI '{$uri}'");
        }

        $this->scheme = isset($parts['scheme']) ? \strtr($parts['scheme'], 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') : '';
        $this->userInfo = $parts['user'] ?? '';
        $this->userInfo .= isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $this->host = isset($parts['host']) ? \strtr($parts['host'], 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') : '';
        $this->port = isset($parts['port']) ? $this->validatePort($parts['port']) : null;
        $this->path = isset($parts['path']) ? $this->validate(self::REG_PATH, $parts['path']) : '';
        $this->query = isset($parts['query']) ? $this->validate(self::REG_QUERY_FRAGMENT, $parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->validate(self::REG_QUERY_FRAGMENT, $parts['fragment']) : '';
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if ('' === $this->host) {
            return '';
        }

        $authority = $this->host;
        if ('' !== $this->userInfo) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if (null !== $this->port) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        $path = $this->path;
        if ('' !== $path && '/' !== $path[0]) {
            if ('' !== $this->host) {
                $path = '/' . $path;
            }
        } elseif (isset($path[1]) && '/' === $path[1]) {
            $path = '/' . \ltrim($path);
        }

        return $path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $scheme = \strtr($scheme, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
        if ($scheme === $this->scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $scheme;

        return $new;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $userInfo = $this->validate(self::REG_USER_INFO, $user);
        if (null !== $password && '' !== $password) {
            $userInfo .= ':' . $this->validate(self::REG_USER_INFO, $password);
        }

        $new = clone $this;
        $new->userInfo = $userInfo;

        return $new;
    }

    public function withHost(string $host): UriInterface
    {
        $host = \strtr($host, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
        if ($host === $this->host) {
            return $this;
        }

        $new = clone $this;
        $new->host = $host;

        return $new;
    }

    public function withPort(?int $port): UriInterface
    {
        $port = $this->validatePort($port);
        if ($port === $this->port) {
            return $this;
        }

        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    public function withPath(string $path): UriInterface
    {
        $path = $this->validate(self::REG_PATH, $path);
        if ($path === $this->path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    public function withQuery(string $query): UriInterface
    {
        $query = $this->validate(self::REG_QUERY_FRAGMENT, $query);
        if ($query === $this->query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    public function withFragment(string $fragment): UriInterface
    {
        $fragment = $this->validate(self::REG_QUERY_FRAGMENT, $fragment);
        if ($fragment === $this->fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    public function __toString(): string
    {
        $uri = '';
        if ('' !== $this->getScheme()) {
            $uri .= $this->getScheme() . ':';
        }

        if ('' !== $this->getAuthority()) {
            $uri .= '//' . $this->getAuthority();
        }

        $uri .= $this->getPath();
        $uri .= '' !== $this->getQuery() ? '?' . $this->getQuery() : '';
        $uri .= '' !== $this->getFragment() ? '#' . $this->getFragment() : '';

        return $uri;
    }

    private function validatePort(?int $port): ?int
    {
        if (null === $port) {
            return null;
        }

        if (0x0 > $port || 0xFFFF < $port) {
            throw new \InvalidArgumentException("Invalid port '{$port}'");
        }

        return $this->isStandardPort($this->scheme, $port) ? null : $port;
    }

    private function isStandardPort(string $scheme, int $port): bool
    {
        return isset(self::STANDARD_PORT[$scheme]) && self::STANDARD_PORT[$scheme] === $port;
    }

    private function validate(string $pattern, string $value): string
    {
        return (string) \preg_replace_callback(
            $pattern,
            function (array $matches): string {
                return \rawurlencode($matches[0]);
            },
            $value
        );
    }
}
