<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'first_name' => 'Example',
        'last_name' => 'User',
        'middle_name' => null,
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'gender' => 'male',
        'civil_status' => 'single',
        'birthdate' => '2001-01-01',
        'contact_number' => '09123123123',
        'address' => 'my address',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
