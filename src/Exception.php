<?php

namespace OpenApi;

class Exception extends \Exception
{
    private mixed $serverResponse = null;
    private mixed $headers = null;
    private mixed $rawResponse = null;
    private ?int $httpCode = null;

    public function setServerResponse(mixed $response, mixed $headers = null, mixed $rawResponse = null, ?int $httpCode = null): void
    {
        $this->serverResponse = $response;
        $this->headers = $headers;
        $this->rawResponse = $rawResponse;
        $this->httpCode = $httpCode;
    }

    public function getServerResponse(): mixed
    {
        return $this->serverResponse;
    }

    public function getHeaders(): mixed
    {
        return $this->headers;
    }

    public function getRawResponse(): mixed
    {
        return $this->rawResponse;
    }

    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }
}