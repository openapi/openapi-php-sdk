<?php

namespace OpenApi;

/**
 * Custom exception for OpenAPI SDK
 * Stores HTTP response details for better error handling
 */
class Exception extends \Exception
{
    private mixed $serverResponse = null;
    private mixed $headers = null;
    private mixed $rawResponse = null;
    private ?int $httpCode = null;

    /**
     * Store server response details
     * TODO: Utilize this method in Client and OauthClient to provide structured error context
     *
     * @param mixed $response Parsed server response
     * @param mixed $headers Response headers
     * @param mixed $rawResponse Raw response body
     * @param int|null $httpCode HTTP status code
     */
    public function setServerResponse(mixed $response, mixed $headers = null, mixed $rawResponse = null, ?int $httpCode = null): void
    {
        $this->serverResponse = $response;
        $this->headers = $headers;
        $this->rawResponse = $rawResponse;
        $this->httpCode = $httpCode;
    }

    /**
     * Get parsed server response
     */
    public function getServerResponse(): mixed
    {
        return $this->serverResponse;
    }

    /**
     * Get response headers
     */
    public function getHeaders(): mixed
    {
        return $this->headers;
    }

    /**
     * Get raw response body
     */
    public function getRawResponse(): mixed
    {
        return $this->rawResponse;
    }

    /**
     * Get HTTP status code
     */
    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }
}