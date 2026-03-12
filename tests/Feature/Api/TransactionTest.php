<?php

use App\Enums\TransactionStatus;
use App\Models\{Gateway, Transaction, User};
use Illuminate\Support\Facades\Http;

test('listar transações exige autenticação', function () {
    $this->getJson('/api/transactions')->assertUnauthorized();
});

test('usuário autenticado pode listar transações', function () {
    Transaction::factory()->count(2)->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/transactions');

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

test('mostrar transação exige autenticação', function () {
    $transaction = Transaction::factory()->create();
    $this->getJson('/api/transactions/' . $transaction->id)->assertUnauthorized();
});

test('usuário autenticado pode mostrar transação', function () {
    $user        = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'amount' => 15000,
        'status' => TransactionStatus::PAID,
    ]);
    $transaction->load(['client', 'gateway', 'products']);

    $response = $this->actingAs($user)->getJson('/api/transactions/' . $transaction->id);

    $response->assertSuccessful()
        ->assertJsonPath('data.amount', 15000)
        ->assertJsonPath('data.status', TransactionStatus::PAID->value);
});

test('reembolso exige autenticação', function () {
    $transaction = Transaction::factory()->paid()->create();
    $this->postJson('/api/transactions/' . $transaction->id . '/refund')
        ->assertUnauthorized();
});

test('reembolso exige manage-finances', function () {
    $transaction = Transaction::factory()->paid()->create();
    $user        = User::factory()->create(['role' => \App\Enums\UserRole::USER]);
    $this->actingAs($user)->postJson('/api/transactions/' . $transaction->id . '/refund')
        ->assertForbidden();
});

test('reembolso retorna sucesso quando gateway processa', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(['token' => 'fake-token'], 200)
            ->push([], 200),
    ]);

    $gateway     = Gateway::factory()->create(['name' => 'GATEWAY_1']);
    $transaction = Transaction::factory()->create([
        'status'      => TransactionStatus::PAID,
        'gateway_id'  => $gateway->id,
        'external_id' => 'ext-123',
    ]);
    $finance = User::factory()->finance()->create();

    $response = $this->actingAs($finance)->postJson('/api/transactions/' . $transaction->id . '/refund');

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Reembolso efetuado com sucesso.')
        ->assertJsonPath('transaction.status', TransactionStatus::REFUNDED->value);
});

test('reembolso retorna 422 quando transação não está PAID', function () {
    $transaction = Transaction::factory()->create(['status' => TransactionStatus::PENDING]);
    $finance     = User::factory()->finance()->create();

    $response = $this->actingAs($finance)->postJson('/api/transactions/' . $transaction->id . '/refund');

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'Apenas transações com estado PAID podem ser reembolsadas.');
});

test('reembolso retorna 422 quando gateway falha', function () {
    Http::fake(['*' => Http::response(['erro' => 'Falha'], 500)]);

    $gateway     = Gateway::factory()->create(['name' => 'GATEWAY_1']);
    $transaction = Transaction::factory()->create([
        'status'      => TransactionStatus::PAID,
        'gateway_id'  => $gateway->id,
        'external_id' => 'ext-123',
    ]);
    $finance = User::factory()->finance()->create();

    $response = $this->actingAs($finance)->postJson('/api/transactions/' . $transaction->id . '/refund');

    $response->assertUnprocessable()
        ->assertJsonStructure(['message']);
});
