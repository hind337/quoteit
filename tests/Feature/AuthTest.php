<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('sign up', function () {
    test('a visitor can create an account and is logged in', function () {
        $response = $this->post('/signup', [
            'name' => 'Sara Ali',
            'email' => 'sara@example.com',
            'password' => 'sunshine123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::firstWhere('email', 'sara@example.com');
        expect($user->name)->toBe('Sara Ali')
            ->and(Hash::check('sunshine123', $user->password))->toBeTrue()
            ->and($user->password)->not->toBe('sunshine123');
    });

    test('a name written in Arabic letters is accepted', function () {
        $this->post('/signup', [
            'name' => 'سارة',
            'email' => 'sara@example.com',
            'password' => 'sunshine123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['name' => 'سارة']);
    });

    test('invalid details are rejected', function (array $details, string $field) {
        $this->post('/signup', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => 'sunshine123',
            ...$details,
        ])->assertSessionHasErrors($field);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    })->with([
        'name with numbers' => [['name' => 'Sara2'], 'name'],
        'name too short' => [['name' => 'S'], 'name'],
        'invalid email' => [['email' => 'not-an-email'], 'email'],
        'password too short' => [['password' => 'short'], 'password'],
    ]);

    test('an email that already has an account is rejected', function () {
        User::factory()->create(['email' => 'sara@example.com']);

        $this->post('/signup', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => 'sunshine123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    });
});

describe('log in', function () {
    test('a user can log in with the right email and password', function () {
        $user = User::factory()->create(['password' => 'sunshine123']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'sunshine123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    });

    test('a wrong password does not log the user in', function () {
        $user = User::factory()->create(['password' => 'sunshine123']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });
});

describe('log out', function () {
    test('a logged-in user can log out', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('home'));

        $this->assertGuest();
    });

    test('logged-in users are sent away from the log in and sign up pages', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect();
        $this->actingAs($user)->get('/signup')->assertRedirect();
    });
});

test('the navbar shows the name of the logged-in user', function () {
    $user = User::factory()->create(['name' => 'Maryam']);

    $this->actingAs($user)->get('/')
        ->assertSee('Maryam')
        ->assertSee('Log out')
        ->assertDontSee('Sign Up');
});

test('visitors see log in and sign up links instead of a name', function () {
    $this->get('/')->assertSee('Log In')->assertSee('Sign Up')->assertDontSee('Log out');
});
