<?php

namespace App\Repositories;

use App\Models\PlayerNote;
use App\Models\User;
use App\Repositories\Contracts\PlayerNoteRepositoryInterface;
use Illuminate\Support\Collection;

class PlayerNoteRepository implements PlayerNoteRepositoryInterface
{
    /**
     * Get every note written about the given player, newest first,
     * with the author eager-loaded.
     *
     * @return Collection<int, PlayerNote>
     */
    public function getForPlayer(User $player): Collection
    {
        return $player->receivedNotes()
            ->with('author')
            ->latest()
            ->get();
    }

    /**
     * Store a new note written by an author about a player.
     */
    public function create(User $player, User $author, string $content): PlayerNote
    {
        return $player->receivedNotes()->create([
            'author_id' => $author->id,
            'content' => $content,
        ]);
    }
}
