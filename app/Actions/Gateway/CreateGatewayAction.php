<?php

namespace App\Actions\Gateway;

use App\Models\Gateway;

class CreateGatewayAction
{
    public function handle(array $data): Gateway
    {
        return Gateway::create($data);
    }
}
