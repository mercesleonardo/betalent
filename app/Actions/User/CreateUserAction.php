<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function handle(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }
}
