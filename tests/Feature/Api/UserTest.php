<?php

use App\Enums\UserRole;
use App\Models\User;

test('listar usuários exige autenticação', function () {
    $this->getJson('/api/users')->assertUnauthorized();
});

test('listar usuários exige role manager ou admin', function () {
    $user = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->getJson('/api/users')->assertForbidden();
});

test('manager pode listar usuários', function () {
    User::factory()->count(2)->create();
    $manager = User::factory()->manager()->create();

    $response = $this->actingAs($manager)->getJson('/api/users');

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

test('admin pode listar usuários', function () {
    $admin    = User::factory()->admin()->create();
    $response = $this->actingAs($admin)->getJson('/api/users');
    $response->assertSuccessful();
});

test('criar usuário exige autenticação', function () {
    $this->postJson('/api/users', [
        'name'     => 'Novo',
        'email'    => 'novo@betalent.tech',
        'password' => 'password',
        'role'     => 'user',
    ])->assertUnauthorized();
});

test('manager pode criar usuário', function () {
    $manager = User::factory()->manager()->create();
    $payload = [
        'name'     => 'Novo User',
        'email'    => 'novo@betalent.tech',
        'password' => 'secret123',
        'role'     => 'user',
    ];

    $response = $this->actingAs($manager)->postJson('/api/users', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Novo User')
        ->assertJsonPath('data.email', 'novo@betalent.tech')
        ->assertJsonPath('data.role', 'user');
    $this->assertDatabaseHas('users', ['email' => 'novo@betalent.tech']);
});

test('criar usuário exige name email password role', function () {
    $manager  = User::factory()->manager()->create();
    $response = $this->actingAs($manager)->postJson('/api/users', []);
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
});

test('criar usuário exige role válida', function () {
    $manager  = User::factory()->manager()->create();
    $response = $this->actingAs($manager)->postJson('/api/users', [
        'name'     => 'Novo',
        'email'    => 'novo@betalent.tech',
        'password' => 'secret',
        'role'     => 'invalid',
    ]);
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['role']);
});

test('finance não pode criar usuário', function () {
    $finance  = User::factory()->finance()->create();
    $response = $this->actingAs($finance)->postJson('/api/users', [
        'name'     => 'Novo',
        'email'    => 'novo@betalent.tech',
        'password' => 'secret',
        'role'     => 'user',
    ]);
    $response->assertForbidden();
});

test('mostrar usuário exige manage-users', function () {
    $target = User::factory()->create();
    $user   = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->getJson('/api/users/' . $target->id)->assertForbidden();
});

test('manager pode mostrar usuário', function () {
    $manager  = User::factory()->manager()->create();
    $target   = User::factory()->create(['name' => 'Alvo']);
    $response = $this->actingAs($manager)->getJson('/api/users/' . $target->id);
    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Alvo');
});

test('atualizar usuário exige manage-users', function () {
    $target = User::factory()->create();
    $user   = User::factory()->finance()->create();
    $this->actingAs($user)->putJson('/api/users/' . $target->id, [
        'name' => 'Updated',
    ])->assertForbidden();
});

test('manager pode atualizar usuário', function () {
    $manager  = User::factory()->manager()->create();
    $target   = User::factory()->create(['name' => 'Antigo']);
    $response = $this->actingAs($manager)->putJson('/api/users/' . $target->id, [
        'name' => 'Atualizado',
    ]);
    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Atualizado');
});

test('remover usuário exige manage-users', function () {
    $target = User::factory()->create();
    $user   = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->deleteJson('/api/users/' . $target->id)->assertForbidden();
});

test('manager pode remover usuário', function () {
    $manager  = User::factory()->manager()->create();
    $target   = User::factory()->create();
    $response = $this->actingAs($manager)->deleteJson('/api/users/' . $target->id);
    $response->assertNoContent();
    $this->assertSoftDeleted('users', ['id' => $target->id]);
});
