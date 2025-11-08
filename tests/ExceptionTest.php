<?php

use OpenApi\Exception;
use PHPUnit\Framework\TestCase;

final class ExceptionTest extends TestCase
{
    public function testExceptionCreation(): void
    {
        $message = 'Test exception message';
        $code = 400;
        
        $exception = new Exception($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testSetServerResponse(): void
    {
        $exception = new Exception('Test message');
        
        $response = ['error' => 'Server error'];
        $headers = 'Content-Type: application/json';
        $rawResponse = '{"error":"Server error"}';
        $httpCode = 500;
        
        $exception->setServerResponse($response, $headers, $rawResponse, $httpCode);
        
        $this->assertEquals($response, $exception->getServerResponse());
        $this->assertEquals($headers, $exception->getHeaders());
        $this->assertEquals($rawResponse, $exception->getRawResponse());
        $this->assertEquals($httpCode, $exception->getHttpCode());
    }
}