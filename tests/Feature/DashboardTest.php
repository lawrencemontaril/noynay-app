<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');

    $response = $this->get('/admin/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated admin users can visit the dashboard', function () {
    $user = User::factory()->adminRole()->create();

    $this->actingAs($user);

    $response = $this->get('/admin/dashboard');
    $response->assertStatus(200);
});

test('authenticated patient users can visit the dashboard', function () {
    $user = User::factory()->patientRole()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
