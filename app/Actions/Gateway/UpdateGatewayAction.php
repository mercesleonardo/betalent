<?php

namespace App\Actions\Gateway;

use App\Models\Gateway;

class UpdateGatewayAction
{
    public function handle(Gateway $gateway, array $data): Gateway
    {
        $gateway->update($data);

        return $gateway->fresh();
    }
}
