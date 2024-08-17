<?php

/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace Kvitrvn\Psr7\Tests;

use Kvitrvn\Psr7\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Uri::class)]
class UriTest extends TestCase
{
    public function testUriParse(): void
    {
        $uri = new Uri('http://username:password@hostname:9090/path/to/resource?arg=value&q=search#anchor');

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('username:password', $uri->getUserInfo());
        $this->assertSame('hostname', $uri->getHost());
        $this->assertSame(9090, $uri->getPort());
        $this->assertSame('/path/to/resource', $uri->getPath());
        $this->assertSame('arg=value&q=search', $uri->getQuery());
        $this->assertSame('anchor', $uri->getFragment());
        $this->assertSame('username:password@hostname:9090', $uri->getAuthority());

        $this->assertSame(
            'http://username:password@hostname:9090/path/to/resource?arg=value&q=search#anchor',
            (string) $uri
        );
    }

    public function testBuildUri(): void
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withUserInfo('username:password')
            ->withHost('hostname')
            ->withPort(9090)
            ->withPath('/path/to/resource')
            ->withQuery('arg=value&q=search')
            ->withFragment('anchor');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('username:password', $uri->getUserInfo());
        $this->assertSame('hostname', $uri->getHost());
        $this->assertSame(9090, $uri->getPort());
        $this->assertSame('/path/to/resource', $uri->getPath());
        $this->assertSame('arg=value&q=search', $uri->getQuery());
        $this->assertSame('anchor', $uri->getFragment());
        $this->assertSame('username:password@hostname:9090', $uri->getAuthority());
    }

    #[DataProvider('getValidUris')]
    public function testValidUris(string $input): void
    {
        $uri = new Uri($input);

        $this->assertSame($input, (string) $uri);
    }

    #[DataProvider('getInvalidUris')]
    public function testInvalidUris(string $invalidUri): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unable to parse URI '{$invalidUri}'");

        new Uri($invalidUri);
    }

    #[DataProvider('getInvalidPorts')]
    public function testInvalidPort(int $invalidPort): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid port '{$invalidPort}'");

        (new Uri())->withPort($invalidPort);
    }

    /**
     * @return array<int,string[]>
     */
    public static function getValidUris(): array
    {
        return [
            ['http://www.example.com'],
            ['https://www.example.com/path/to/resource?query=string#fragment'],
            ['ftp://username:password@ftp.example.com/file.txt'],
            ['mailto:user@example.com?subject=Subject&body=Message'],
            ['data:text/plain;base64,SGVsbG8sIFdvcmxkIQ=='],
            ['urn:isbn:0451450523'],
            ['tel:+1234567890'],
            ['tel:555-1234'],
            ['ldap://[2001:db8::7]/c=GB?objectClass'],
            ['urn:uuid:123e4567-e89b-12d3-a456-426655440000'],
            ['jar:http://www.example.com/myarchive.jar!/mypackage/myclass.class'],
            ['irc://irc.example.com/channel'],
            ['irc://irc.example.com:6667/channel'],
            ['magnet:?xt=urn:btih:12345&dn=Example&tr=http://tracker.example.com'],
            ['ssh://user@hostname:22/path/to/repo.git'],
            ['sip:user@domain.com'],
            ['sips:user@domain.com'],
            ['rtsp://example.com/media.mp4'],
            ['ws://example.com/socket'],
            ['wss://example.com/socket'],
            ['geo:37.786971,-122.399677'],
            ['geo:37.786971,-122.399677;u=35'],
            ['smb://server/folder/file.txt'],
            ['cal://example.com/event'],
            ['cal://user@domain.com/event'],
            ['ftpes://ftp.example.com/file.txt'],
            [''],
        ];
    }

    /**
     * @return array<int, string[]>
     */
    public static function getInvalidUris(): array
    {
        return [
            ['https://'],
            ['http://'],
            ['ftp://'],
            ['urn://'],
            ['https://host:invalid'],
        ];
    }

    /**
     * @return array<int, int[]>
     */
    public static function getInvalidPorts(): array
    {
        return [
            [-1],
            [1333337],
        ];
    }
}
