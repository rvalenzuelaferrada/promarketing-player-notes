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
