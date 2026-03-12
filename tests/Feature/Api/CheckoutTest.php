<?php

use App\Enums\TransactionStatus;
use App\Models\{Gateway, Product};
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->product1 = Product::factory()->create(['name' => 'Produto A', 'amount' => 10000]);
    $this->product2 = Product::factory()->create(['name' => 'Produto B', 'amount' => 2500]);
    Gateway::factory()->create(['name' => 'GATEWAY_1', 'is_active' => true, 'priority' => 1]);
});

test('checkout realiza compra e retorna transação quando gateway aprova', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(['token' => 'fake-token'], 200)   // login
            ->push(['id' => 'ext-123'], 201),        // transactions
    ]);

    $response = $this->postJson('/api/checkout', [
        'products' => [
            ['id' => $this->product1->id, 'quantity' => 2],
            ['id' => $this->product2->id, 'quantity' => 1],
        ],
        'card_name'   => 'João Silva',
        'card_email'  => 'joao@email.com',
        'card_number' => '5569000000006063',
        'card_cvv'    => '010',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Compra realizada com sucesso!')
        ->assertJsonPath('transaction.status', TransactionStatus::PAID->value)
        ->assertJsonPath('transaction.amount', 22500)
        ->assertJsonPath('transaction.external_id', 'ext-123');
});

test('checkout retorna 422 quando todos os gateways falham', function () {
    Http::fake([
        '*/login'        => Http::response(['token' => 'fake-token'], 200),
        '*/transactions' => Http::response(['erro' => 'Cartão recusado'], 422),
    ]);

    $response = $this->postJson('/api/checkout', [
        'products'    => [['id' => $this->product1->id, 'quantity' => 1]],
        'card_name'   => 'João',
        'card_email'  => 'joao@email.com',
        'card_number' => '5569000000006063',
        'card_cvv'    => '010',
    ]);

    $response->assertUnprocessable()
        ->assertJsonStructure(['message']);
});

test('checkout exige products com id e quantity', function () {
    $response = $this->postJson('/api/checkout', [
        'products'    => [],
        'card_name'   => 'João',
        'card_email'  => 'joao@email.com',
        'card_number' => '5569000000006063',
        'card_cvv'    => '010',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['products']);
});

test('checkout exige product id existente', function () {
    Http::fake();

    $response = $this->postJson('/api/checkout', [
        'products'    => [['id' => 99999, 'quantity' => 1]],
        'card_name'   => 'João',
        'card_email'  => 'joao@email.com',
        'card_number' => '5569000000006063',
        'card_cvv'    => '010',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['products.0.id']);
});

test('checkout exige dados do cartão', function () {
    $response = $this->postJson('/api/checkout', [
        'products' => [['id' => $this->product1->id, 'quantity' => 1]],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['card_name', 'card_email', 'card_number', 'card_cvv']);
});

test('checkout exige card_number com 16 dígitos e card_cvv 3 ou 4', function () {
    $response = $this->postJson('/api/checkout', [
        'products'    => [['id' => $this->product1->id, 'quantity' => 1]],
        'card_name'   => 'João',
        'card_email'  => 'joao@email.com',
        'card_number' => '123',
        'card_cvv'    => '1',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['card_number', 'card_cvv']);
});
