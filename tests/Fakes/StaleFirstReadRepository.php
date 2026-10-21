<?php

namespace Tests\Fakes;

use Illuminate\Cache\Repository;

class StaleFirstReadRepository extends Repository
{
    public ?array $staleView = null;

    private bool $served = false;

    public function get($key, $default = null): mixed
    {
        if ($this->staleView !== null && !$this->served) {
            $this->served = true;

            return $this->staleView;
        }

        return parent::get($key, $default);
    }
}
