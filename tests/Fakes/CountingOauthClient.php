<?php

namespace Tests\Fakes;

use Openapi\ApiException;
use Openapi\OauthClient;

class CountingOauthClient extends OauthClient
{
    public int $calls = 0;

    public bool $fail = false;

    public ?int $expireIn = null;

    public bool $omitExpire = false;

    public ?string $rawResponse = null;

    public function createToken(array $scopes, int $ttl = 3600): string
    {
        $this->calls++;

        if ($this->fail) {
            throw new ApiException('HTTP Error 429');
        }

        if ($this->rawResponse !== null) {
            return $this->rawResponse;
        }

        $response = [
            'scopes' => $scopes,
            'token' => 'tok-' . $this->calls,
            'success' => true,
            'message' => '',
            'error' => null,
        ];

        if (!$this->omitExpire) {
            $response['expire'] = time() + ($this->expireIn ?? $ttl);
        }

        return (string) json_encode($response);
    }
}
