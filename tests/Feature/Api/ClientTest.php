<?php

use App\Models\{Client, Transaction, User};

test('listar clientes exige autenticação', function () {
    $this->getJson('/api/clients')->assertUnauthorized();
});

test('usuário autenticado pode listar clientes', function () {
    Client::factory()->count(2)->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/clients');

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

test('mostrar cliente exige autenticação', function () {
    $client = Client::factory()->create();
    $this->getJson('/api/clients/' . $client->id)->assertUnauthorized();
});

test('usuário autenticado pode mostrar cliente com transações', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->create(['name' => 'Maria', 'email' => 'maria@email.com']);
    Transaction::factory()->count(2)->create(['client_id' => $client->id]);

    $response = $this->actingAs($user)->getJson('/api/clients/' . $client->id);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Maria')
        ->assertJsonPath('data.email', 'maria@email.com')
        ->assertJsonStructure(['data' => ['transactions']]);
});

test('mostrar cliente inexistente retorna 404', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->getJson('/api/clients/99999')->assertNotFound();
});
