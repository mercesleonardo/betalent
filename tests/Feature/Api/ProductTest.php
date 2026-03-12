<?php

use App\Enums\UserRole;
use App\Models\{Product, User};

test('listar produtos exige autenticação', function () {
    $this->getJson('/api/products')->assertUnauthorized();
});

test('usuário autenticado pode listar produtos', function () {
    Product::factory()->count(2)->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/products');

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

test('criar produto exige manage-products', function () {
    $user = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->postJson('/api/products', [
        'name'   => 'Produto X',
        'amount' => 5000,
    ])->assertForbidden();
});

test('manager pode criar produto', function () {
    $manager  = User::factory()->manager()->create();
    $response = $this->actingAs($manager)->postJson('/api/products', [
        'name'   => 'Produto Novo',
        'amount' => 9999,
    ]);
    $response->assertCreated()
        ->assertJsonPath('data.name', 'Produto Novo')
        ->assertJsonPath('data.amount', 9999);
    $this->assertDatabaseHas('products', ['name' => 'Produto Novo']);
});

test('finance pode criar produto', function () {
    $finance  = User::factory()->finance()->create();
    $response = $this->actingAs($finance)->postJson('/api/products', [
        'name'   => 'Produto Finance',
        'amount' => 1000,
    ]);
    $response->assertCreated();
});

test('criar produto exige name e amount', function () {
    $manager  = User::factory()->manager()->create();
    $response = $this->actingAs($manager)->postJson('/api/products', []);
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'amount']);
});

test('qualquer autenticado pode mostrar produto', function () {
    $product  = Product::factory()->create(['name' => 'Produto A', 'amount' => 1000]);
    $user     = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/products/' . $product->id);
    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Produto A');
});

test('atualizar produto exige manage-products', function () {
    $product = Product::factory()->create();
    $user    = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->putJson('/api/products/' . $product->id, [
        'name' => 'Updated',
    ])->assertForbidden();
});

test('manager pode atualizar produto', function () {
    $manager  = User::factory()->manager()->create();
    $product  = Product::factory()->create(['name' => 'Antigo', 'amount' => 1000]);
    $response = $this->actingAs($manager)->putJson('/api/products/' . $product->id, [
        'name'   => 'Atualizado',
        'amount' => 2000,
    ]);
    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Atualizado')
        ->assertJsonPath('data.amount', 2000);
});

test('remover produto exige manage-products', function () {
    $product = Product::factory()->create();
    $user    = User::factory()->create(['role' => UserRole::USER]);
    $this->actingAs($user)->deleteJson('/api/products/' . $product->id)->assertForbidden();
});

test('manager pode remover produto', function () {
    $manager  = User::factory()->manager()->create();
    $product  = Product::factory()->create();
    $response = $this->actingAs($manager)->deleteJson('/api/products/' . $product->id);
    $response->assertNoContent();
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
