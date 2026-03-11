<?php

namespace App\Observers;

use App\Models\Gateway;
use Illuminate\Support\Facades\Cache;

class GatewayObserver
{
    public const LIST_CACHE_KEY = 'gateways.list';

    public function created(Gateway $gateway): void
    {
        $this->invalidateCache();
    }

    public function updated(Gateway $gateway): void
    {
        $this->invalidateCache();
    }

    public function deleted(Gateway $gateway): void
    {
        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
    }
}
