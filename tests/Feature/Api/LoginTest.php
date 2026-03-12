<?php

use App\Models\User;

test('login retorna token e user com credenciais válidas', function () {
    User::factory()->create([
        'email'    => 'user@betalent.tech',
        'password' => 'secret123',
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'user@betalent.tech',
        'password' => 'secret123',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ])
        ->assertJsonPath('user.email', 'user@betalent.tech');
    expect($response->json('token'))->not->toBeEmpty();
});

test('login falha com senha incorreta', function () {
    User::factory()->create([
        'email' => 'user@betalent.tech',
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'user@betalent.tech',
        'password' => 'wrong',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('login falha com email inexistente', function () {
    $response = $this->postJson('/api/login', [
        'email'    => 'unknown@betalent.tech',
        'password' => 'any',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('login exige email e password', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('login exige email válido', function () {
    $response = $this->postJson('/api/login', [
        'email'    => 'not-an-email',
        'password' => 'secret',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
