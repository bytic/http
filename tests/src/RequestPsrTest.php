<?php

namespace Nip\Http\Tests;

use Nip\Http\Request;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestPsrTest extends AbstractTest
{
    public function testEmptyRequestCanBeConverted()
    {
        $psr = (new Request())->getPRSRequest();

        static::assertInstanceOf(ServerRequestInterface::class, $psr);
        static::assertSame('/', (string) $psr->getUri());
    }

    public function testRequestExtendsSymfonyAndProducesPsrSnapshot()
    {
        $request = Request::create('https://example.com/');

        static::assertInstanceOf(SymfonyRequest::class, $request);
        static::assertInstanceOf(ServerRequestInterface::class, $request->getPRSRequest());
    }

    public function testPsrRequestWrapsTheSymfonyRequestAndRetainsRequestHelpers()
    {
        $request = Request::create('https://example.com/');
        $psrRequest = $request->getPSRRequest();

        static::assertNotSame($request, $psrRequest->getSymfonyRequest());
        static::assertInstanceOf(Request::class, $psrRequest->getSymfonyRequest());

        $psrRequest->setModuleName('admin');
        $psrRequest->attributes->set('route', 'dashboard');

        static::assertSame('admin', $psrRequest->getSymfonyRequest()->getModuleName());
        static::assertSame('dashboard', $psrRequest->getSymfonyRequest()->attributes->get('route'));
    }

    public function testCorrectlySpelledAliasAndProtocolVersion()
    {
        $request = Request::create('https://example.com/', 'GET', [], [], [], ['SERVER_PROTOCOL' => 'HTTP/2.0']);

        static::assertSame('2.0', $request->getPSRRequest()->getProtocolVersion());
        static::assertSame('2.0', $request->getPRSRequest()->getProtocolVersion());
    }

    public function testJsonBodyIsParsedByBridge()
    {
        $request = Request::create('https://example.com/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{"answer":42}');

        static::assertSame(['answer' => 42], $request->getPRSRequest()->getParsedBody());
    }

    public function testUriIsAnObject()
    {
        $request = Request::create('https://example.com/path?foo=bar')->getPRSRequest();

        static::assertInstanceOf(UriInterface::class, $request->getUri());
        static::assertSame('https://example.com/path?foo=bar', (string) $request->getUri());
    }

    public function testWithUriIsImmutableAndUpdatesHost()
    {
        $request = Request::create('https://example.com/old')->getPRSRequest();
        $uri = new Uri('https://other.example/new?foo=bar');
        $modified = $request->withUri($uri);

        static::assertNotSame($request, $modified);
        static::assertSame('https://example.com/old', (string) $request->getUri());
        static::assertSame((string) $uri, (string) $modified->getUri());
        static::assertSame('example.com', $request->getHeaderLine('Host'));
        static::assertSame('other.example', $modified->getHeaderLine('Host'));
    }

    public function testWithUriCanPreserveHost()
    {
        $request = Request::create('https://example.com/old')->getPRSRequest();
        $modified = $request->withUri(new Uri('https://other.example/new'), true);

        static::assertNotSame($request, $modified);
        static::assertSame('example.com', $request->getHeaderLine('Host'));
        static::assertSame('example.com', $modified->getHeaderLine('Host'));
        static::assertSame('https://other.example/new', (string) $modified->getUri());
    }

    public function testWithMethodIsImmutable()
    {
        $request = Request::create('https://example.com/', 'GET')->getPRSRequest();
        $modified = $request->withMethod('POST');

        static::assertNotSame($request, $modified);
        static::assertSame('GET', $request->getMethod());
        static::assertSame('POST', $modified->getMethod());
    }

    public function testWithHeaderIsImmutable()
    {
        $symfony = Request::create('https://example.com/');
        $symfony->headers->set('X-Test', 'original');
        $request = $symfony->getPRSRequest();
        $modified = $request->withHeader('x-test', ['new', 'second']);

        static::assertNotSame($request, $modified);
        static::assertSame(['original'], $request->getHeader('X-Test'));
        static::assertSame(['new', 'second'], $modified->getHeader('X-Test'));
    }

    public function testWithQueryParamsIsImmutable()
    {
        $request = Request::create('https://example.com/?old=value')->getPRSRequest();
        $modified = $request->withQueryParams(['new' => 'value']);

        static::assertNotSame($request, $modified);
        static::assertSame(['old' => 'value'], $request->getQueryParams());
        static::assertSame(['new' => 'value'], $modified->getQueryParams());
    }

    public function testWithAttributeIsImmutable()
    {
        $symfony = Request::create('https://example.com/');
        $symfony->attributes->set('key', 'original');
        $request = $symfony->getPRSRequest();
        $modified = $request->withAttribute('key', 'new');

        static::assertNotSame($request, $modified);
        static::assertSame('original', $request->getAttribute('key'));
        static::assertSame('new', $modified->getAttribute('key'));
        static::assertSame(['key' => 'new'], $modified->getAttributes());
    }

    public function testWithBodyIsImmutable()
    {
        $request = Request::create('https://example.com/', 'POST', [], [], [], [], 'original')->getPRSRequest();
        $body = Stream::create('new');
        $modified = $request->withBody($body);

        static::assertNotSame($request, $modified);
        static::assertSame('original', (string) $request->getBody());
        static::assertSame($body, $modified->getBody());
        static::assertSame('new', (string) $modified->getBody());
    }

    public function testWithProtocolVersionIsImmutable()
    {
        $request = Request::create('https://example.com/', 'GET', [], [], [], ['SERVER_PROTOCOL' => 'HTTP/1.1'])->getPRSRequest();
        $modified = $request->withProtocolVersion('2.0');

        static::assertNotSame($request, $modified);
        static::assertSame('1.1', $request->getProtocolVersion());
        static::assertSame('2.0', $modified->getProtocolVersion());
    }

    public function testSnapshotCopiesRequestData()
    {
        $upload = new UploadedFile(__FILE__, 'example.txt', 'text/plain', null, true);
        $request = Request::create('https://example.com/path?foo=bar', 'POST', ['form' => 'value'], ['session' => 'cookie'], ['upload' => $upload], ['SERVER_PROTOCOL' => 'HTTP/1.1', 'REMOTE_ADDR' => '192.0.2.1'], 'raw body');
        $request->attributes->set('route', 'example');
        $request->headers->set('X-Test', ['one', 'two']);
        $snapshot = $request->getPRSRequest();

        static::assertSame('POST', $snapshot->getMethod());
        static::assertSame('https://example.com/path?foo=bar', (string) $snapshot->getUri());
        static::assertSame(['one', 'two'], $snapshot->getHeader('X-Test'));
        static::assertSame('example.com', $snapshot->getHeaderLine('Host'));
        static::assertSame(['foo' => 'bar'], $snapshot->getQueryParams());
        static::assertSame(['form' => 'value'], $snapshot->getParsedBody());
        static::assertSame(['session' => 'cookie'], $snapshot->getCookieParams());
        static::assertSame('example', $snapshot->getAttribute('route'));
        static::assertSame('192.0.2.1', $snapshot->getServerParams()['REMOTE_ADDR']);
        static::assertSame('example.txt', $snapshot->getUploadedFiles()['upload']->getClientFilename());
        static::assertSame('text/plain', $snapshot->getUploadedFiles()['upload']->getClientMediaType());
        static::assertSame('raw body', (string) $snapshot->getBody());
        static::assertSame('1.1', $snapshot->getProtocolVersion());
    }

    public function testSnapshotIsIndependentOfSymfonyRequest()
    {
        $request = Request::create('https://example.com/?old=value', 'POST', ['form' => 'original'], ['session' => 'original'], [], [], 'original body');
        $request->attributes->set('key', 'original');
        $request->headers->set('X-Test', 'original');
        $snapshot = $request->getPRSRequest();

        $request->setMethod('PUT');
        $request->query->set('old', 'changed');
        $request->request->set('form', 'changed');
        $request->cookies->set('session', 'changed');
        $request->attributes->set('key', 'changed');
        $request->headers->set('X-Test', 'changed');

        static::assertSame('POST', $snapshot->getMethod());
        static::assertSame(['old' => 'value'], $snapshot->getQueryParams());
        static::assertSame(['form' => 'original'], $snapshot->getParsedBody());
        static::assertSame(['session' => 'original'], $snapshot->getCookieParams());
        static::assertSame('original', $snapshot->getAttribute('key'));
        static::assertSame('original', $snapshot->getHeaderLine('X-Test'));
        static::assertSame('original body', (string) $snapshot->getBody());

        $modified = $snapshot->withMethod('PATCH')->withAttribute('key', 'psr')->withHeader('X-Test', 'psr');
        static::assertSame('PATCH', $modified->getMethod());
        static::assertSame('PUT', $request->getMethod());
        static::assertSame('changed', $request->attributes->get('key'));
        static::assertSame('changed', $request->headers->get('X-Test'));
        static::assertSame('original', $snapshot->getAttribute('key'));
        static::assertSame('original', $snapshot->getHeaderLine('X-Test'));
    }
}