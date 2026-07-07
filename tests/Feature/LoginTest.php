<?php

use App\Models\User;

it('logs a user in and redirects to the panel', function () {
    $user = User::factory()->create(['email' => 'agent@support.test']);

    $this->post(route('login.store'), [
        'email' => 'agent@support.test',
        'password' => 'password',
    ])->assertRedirect(route('player-notes'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    User::factory()->create(['email' => 'agent@support.test']);

    $this->post(route('login.store'), [
        'email' => 'agent@support.test',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('shows the login screen at the root url', function () {
    $this->get('/')->assertOk()->assertSee('Sign in');
});

it('redirects an authenticated user away from the login screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertRedirect(route('player-notes'));
});
