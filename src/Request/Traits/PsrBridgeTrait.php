<?php

namespace Nip\Http\Request\Traits;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UploadedFileInterface;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

/**
 * Class PsrBridgeTrait
 * Add methods to make Symfony Request compliant with PSR
 *
 * @package Nip\Http\Request\Traits
 */
trait PsrBridgeTrait
{
    private $psrRequest;
    private $parsedBodySet = false;
    private $parsedBody;
    private $uploadedFiles;
    private $psrHeadersSnapshot;
    private $psrUriChanged = false;
    private $psrBodyChanged = false;
    private $psrProtocolChanged = false;

    private function resetPsrState(): void
    {
        $this->psrRequest = null;
        $this->parsedBodySet = false;
        $this->parsedBody = null;
        $this->uploadedFiles = null;
        $this->psrHeadersSnapshot = null;
        $this->psrUriChanged = false;
        $this->psrBodyChanged = false;
        $this->psrProtocolChanged = false;
    }

    private function psrRequest(): ServerRequest
    {
        if ($this->psrRequest === null) {
            $version = preg_replace('/^HTTP\//i', '', $this->server->get('SERVER_PROTOCOL', 'HTTP/1.1'));
            $uri = $this->symfonyRequest->getUri();
            if ($this->symfonyRequest->getHost() === '') {
                $uri = $this->symfonyRequest->getRequestUri() ?: '/';
            }

            $this->psrRequest = new ServerRequest($this->symfonyRequest->getMethod(), $uri, $this->headers->all(), $this->symfonyRequest->getContent(), $version, $this->server->all());
            $this->psrHeadersSnapshot = $this->headers->all();
        } elseif ($this->psrHeadersSnapshot !== $this->headers->all()) {
            foreach ($this->psrHeadersSnapshot as $name => $values) {
                if (!$this->headers->has($name)) {
                    $this->psrRequest = $this->psrRequest->withoutHeader($name);
                }
            }
            foreach ($this->headers->all() as $name => $values) {
                if (!isset($this->psrHeadersSnapshot[$name]) || $this->psrHeadersSnapshot[$name] !== $values) {
                    $this->psrRequest = $this->psrRequest->withHeader($name, $values);
                }
            }
            $this->psrHeadersSnapshot = $this->headers->all();
        }
        return $this->psrRequest;
    }

    private function withPsrRequest($request): static
    {
        $new = clone $this;
        $new->psrRequest = $request;
        return $new;
    }
    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->psrRequest()->getProtocolVersion();
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version): static
    {
        $new = $this->withPsrRequest($this->psrRequest()->withProtocolVersion($version));
        $new->psrProtocolChanged = true;
        return $new;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders(): array
    {
        return $this->psrRequest()->getHeaders();
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name): array
    {
        return $this->psrRequest()->getHeader($name);
    }

    public function hasHeader($name): bool
    {
        return $this->psrRequest()->hasHeader($name);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name): string
    {
        return $this->psrRequest()->getHeaderLine($name);
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value): static
    {
        $new = $this->withPsrRequest($this->psrRequest()->withAddedHeader($name, $value));
        $new->headers->set($name, $new->psrRequest->getHeader($name));
        $new->psrHeadersSnapshot = $new->headers->all();
        return $new;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody(): StreamInterface
    {
        return $this->psrRequest()->getBody();
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body): static
    {
        $new = $this->withPsrRequest($this->psrRequest()->withBody($body));
        $new->psrBodyChanged = true;
        $new->json = null;
        return $new;
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget(): string
    {
        return $this->psrRequest()->getRequestTarget();
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     * @param string $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget): static
    {
        return $this->withPsrRequest($this->psrRequest()->withRequestTarget($requestTarget));
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return static
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function getMethod(): string
    {
        return $this->psrRequest()->getMethod();
    }

    public function withMethod($method): static
    {
        $new = $this->withPsrRequest($this->psrRequest()->withMethod($method));
        $new->symfonyRequest->setMethod($method);
        return $new;
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function getUri(): UriInterface
    {
        return $this->psrRequest()->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $new = $this->withPsrRequest($this->psrRequest()->withUri($uri, $preserveHost));
        $new->psrUriChanged = true;
        if ($new->psrRequest->hasHeader('Host')) {
            $new->headers->set('Host', $new->psrRequest->getHeader('Host'));
        }
        $new->psrHeadersSnapshot = $new->headers->all();
        return $new;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutHeader($name): static
    {
        $new = $this->withPsrRequest($this->psrRequest()->withoutHeader($name));
        $new->headers->remove($name);
        $new->psrHeadersSnapshot = $new->headers->all();
        return $new;
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value): static
    {
        $new = $this->withPsrRequest($this->psrRequest()->withHeader($name, $value));
        $new->headers->set($name, $new->psrRequest->getHeader($name));
        $new->psrHeadersSnapshot = $new->headers->all();
        return $new;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->server->all();
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookies->all();
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies): static
    {
        $new = clone $this;
        $new->cookies->replace($cookies);
        return $new;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->query->all();
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *     $_GET.
     * @return static
     */
    public function withQueryParams(array $query): static
    {
        $new = clone $this;
        $new->query->replace($query);
        return $new;
    }


    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles ?? $this->normalizeUploadedFiles($this->files->all());
    }

    private function normalizeUploadedFiles(array $files): array
    {
        $result = [];
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $result[$key] = $this->normalizeUploadedFiles($file);
            } elseif ($file instanceof SymfonyUploadedFile) {
                $result[$key] = new UploadedFile($file->getPathname(), $file->getSize() ?: 0, $file->getError(), $file->getClientOriginalName(), $file->getClientMimeType());
            } elseif ($file instanceof UploadedFileInterface) {
                $result[$key] = $file;
            } elseif ($file !== null) {
                throw new \InvalidArgumentException('Invalid uploaded file');
            }
        }
        return $result;
    }

    private function validateUploadedFiles(array $files): void
    {
        foreach ($files as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
            } elseif (!$file instanceof UploadedFileInterface) {
                throw new \InvalidArgumentException('Invalid uploaded file');
            }
        }
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles): static
    {
        $this->validateUploadedFiles($uploadedFiles);
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        if ($this->parsedBodySet) {
            return $this->parsedBody;
        }
        $body = $this->request->all();
        $type = strtolower($this->headers->get('Content-Type', ''));
        return $body || ($this->getMethod() === 'POST' && (str_starts_with($type, 'application/x-www-form-urlencoded') || str_starts_with($type, 'multipart/form-data'))) ? $body : null;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data): static
    {
        if ($data !== null && !is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException('Parsed body must be an array, object or null');
        }
        $new = clone $this;
        $new->parsedBodySet = true;
        $new->parsedBody = $data;
        if (is_array($data)) {
            $new->request->replace($data);
        }
        return $new;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes(): array
    {
        return $this->attributes->all();
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     * @see getAttributes()
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->has($name) ? $this->attributes->all()[$name] : $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     * @see getAttributes()
     */
    public function withAttribute($name, $value): static
    {
        $new = clone $this;
        $new->attributes->set($name, $value);
        return $new;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @param string $name The attribute name.
     * @return static
     * @see getAttributes()
     */
    public function withoutAttribute($name): static
    {
        $new = clone $this;
        $new->attributes->remove($name);
        return $new;
    }
}
