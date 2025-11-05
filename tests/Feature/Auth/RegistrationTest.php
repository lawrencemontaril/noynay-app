<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post(route('register'), [
        'first_name' => 'Example',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => '@Super123',
        'password_confirmation' => '@Super123',
        'gender' => 'male',
        'civil_status' => 'single',
        'birthdate' => '2001-01-01',
        'contact_number' => '09123123123',
        'address' => 'my address',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    expect($user->hasRole('patient'))->toBeTrue();
    expect($user->patient)->not->toBeNull();
});
