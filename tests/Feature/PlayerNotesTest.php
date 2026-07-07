<?php

use App\Models\PlayerNote;
use App\Models\User;
use Livewire\Livewire;

it('stores a note for the selected player in the database', function () {
    $agent = User::factory()->canAddPlayerNotes()->create();
    $player = User::factory()->create();

    Livewire::actingAs($agent)
        ->test('pages::player-notes.index')
        ->set('selectedPlayerId', $player->id)
        ->set('content', 'Reported for abusive chat.')
        ->call('save')
        ->assertHasNoErrors();

    expect(PlayerNote::query()->where([
        'player_id' => $player->id,
        'author_id' => $agent->id,
        'content' => 'Reported for abusive chat.',
    ])->exists())->toBeTrue();
});

it('shows the latest notes across all players when none is selected', function () {
    $agent = User::factory()->canAddPlayerNotes()->create();
    $player = User::factory()->create(['name' => 'Charlie Player']);
    PlayerNote::factory()->for($player, 'player')->for($agent, 'author')->create([
        'content' => 'Recent cross-player note.',
    ]);

    Livewire::actingAs($agent)
        ->test('pages::player-notes.index')
        ->assertSet('selectedPlayerId', null)
        ->assertSee('Últimas notas')
        ->assertSee('Charlie Player')
        ->assertSee('Recent cross-player note.');
});

it('lists the notes with date, author and content', function () {
    $agent = User::factory()->canAddPlayerNotes()->create(['name' => 'Alice Agent']);
    $player = User::factory()->create();
    PlayerNote::factory()->for($player, 'player')->for($agent, 'author')->create([
        'content' => 'VIP player, handle with care.',
    ]);

    Livewire::actingAs($agent)
        ->test('pages::player-notes.index')
        ->set('selectedPlayerId', $player->id)
        ->assertSee('Alice Agent')
        ->assertSee('VIP player, handle with care.')
        ->assertSee('/1000');
});

it('validates that the note is not empty', function () {
    $agent = User::factory()->canAddPlayerNotes()->create();
    $player = User::factory()->create();

    Livewire::actingAs($agent)
        ->test('pages::player-notes.index')
        ->set('selectedPlayerId', $player->id)
        ->set('content', '')
        ->call('save')
        ->assertHasErrors(['content' => 'required']);

    expect(PlayerNote::query()->count())->toBe(0);
});

it('validates the note maximum length', function () {
    $agent = User::factory()->canAddPlayerNotes()->create();
    $player = User::factory()->create();

    Livewire::actingAs($agent)
        ->test('pages::player-notes.index')
        ->set('selectedPlayerId', $player->id)
        ->set('content', str_repeat('a', 1001))
        ->call('save')
        ->assertHasErrors(['content' => 'max']);
});

it('hides the form and forbids saving for users without permission', function () {
    $viewer = User::factory()->create();
    $player = User::factory()->create();

    Livewire::actingAs($viewer)
        ->test('pages::player-notes.index')
        ->set('selectedPlayerId', $player->id)
        ->assertDontSee('Agregar una nota')
        ->call('save')
        ->assertForbidden();

    expect(PlayerNote::query()->count())->toBe(0);
});

it('requires authentication to reach the panel', function () {
    $this->get(route('player-notes'))->assertRedirect(route('login'));
});

it('renders the panel as a full page inside the layout', function () {
    $agent = User::factory()->canAddPlayerNotes()->create();

    $this->actingAs($agent)
        ->get(route('player-notes'))
        ->assertOk()
        ->assertSee('Panel de soporte');
});
