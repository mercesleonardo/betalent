<?php

use App\Enums\UserRole;
use App\Models\{Gateway, User};

test('listar gateways exige autenticação', function () {
    $this->getJson('/api/gateways')->assertUnauthorized();
});

test('listar gateways exige manage-finances', function () {
    $user = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->getJson('/api/gateways')->assertForbidden();
});

test('finance pode listar gateways ativos', function () {
    Gateway::factory()->create(['name' => 'GATEWAY_1', 'is_active' => true]);
    Gateway::factory()->inactive()->create(['name' => 'GATEWAY_2']);
    $finance = User::factory()->finance()->create();

    $response = $this->actingAs($finance)->getJson('/api/gateways');

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

test('admin pode listar gateways', function () {
    $admin    = User::factory()->admin()->create();
    $response = $this->actingAs($admin)->getJson('/api/gateways');
    $response->assertSuccessful();
});

test('criar gateway exige manage-finances', function () {
    $user = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->postJson('/api/gateways', [
        'name'      => 'GATEWAY_1',
        'is_active' => true,
        'priority'  => 1,
    ])->assertForbidden();
});

test('finance pode criar gateway', function () {
    $finance  = User::factory()->finance()->create();
    $response = $this->actingAs($finance)->postJson('/api/gateways', [
        'name'      => 'GATEWAY_1',
        'is_active' => true,
        'priority'  => 1,
    ]);
    $response->assertCreated()
        ->assertJsonPath('data.name', 'GATEWAY_1')
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.priority', 1);
    $this->assertDatabaseHas('gateways', ['name' => 'GATEWAY_1']);
});

test('criar gateway exige name único, is_active e priority', function () {
    $finance  = User::factory()->finance()->create();
    $response = $this->actingAs($finance)->postJson('/api/gateways', []);
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'is_active', 'priority']);
});

test('mostrar gateway exige manage-finances', function () {
    $gateway = Gateway::factory()->create(['name' => 'GATEWAY_1']);
    $user    = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->getJson('/api/gateways/' . $gateway->id)->assertForbidden();
});

test('finance pode mostrar gateway', function () {
    $finance  = User::factory()->finance()->create();
    $gateway  = Gateway::factory()->create(['name' => 'GATEWAY_1']);
    $response = $this->actingAs($finance)->getJson('/api/gateways/' . $gateway->id);
    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'GATEWAY_1');
});

test('atualizar gateway exige manage-finances', function () {
    $gateway = Gateway::factory()->create();
    $user    = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->putJson('/api/gateways/' . $gateway->id, [
        'priority' => 2,
    ])->assertForbidden();
});

test('finance pode atualizar gateway', function () {
    $finance  = User::factory()->finance()->create();
    $gateway  = Gateway::factory()->create(['name' => 'GATEWAY_1', 'priority' => 1]);
    $response = $this->actingAs($finance)->putJson('/api/gateways/' . $gateway->id, [
        'priority'  => 2,
        'is_active' => false,
    ]);
    $response->assertSuccessful()
        ->assertJsonPath('data.priority', 2)
        ->assertJsonPath('data.is_active', false);
});

test('remover gateway exige manage-finances', function () {
    $gateway = Gateway::factory()->create();
    $user    = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->deleteJson('/api/gateways/' . $gateway->id)->assertForbidden();
});

test('finance pode remover gateway', function () {
    $finance  = User::factory()->finance()->create();
    $gateway  = Gateway::factory()->create();
    $response = $this->actingAs($finance)->deleteJson('/api/gateways/' . $gateway->id);
    $response->assertNoContent();
    $this->assertDatabaseMissing('gateways', ['id' => $gateway->id]);
});
