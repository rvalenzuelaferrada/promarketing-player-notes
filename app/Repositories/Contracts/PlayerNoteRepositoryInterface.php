<?php

namespace App\Repositories\Contracts;

use App\Models\PlayerNote;
use App\Models\User;
use Illuminate\Support\Collection;

interface PlayerNoteRepositoryInterface
{
    /**
     * Get every note written about the given player, newest first,
     * with the author eager-loaded.
     *
     * @return Collection<int, PlayerNote>
     */
    public function getForPlayer(User $player): Collection;

    /**
     * Get the most recently added notes across all players, newest first,
     * with the author and player eager-loaded.
     *
     * @return Collection<int, PlayerNote>
     */
    public function getLatest(int $limit = 10): Collection;

    /**
     * Store a new note written by an author about a player.
     */
    public function create(User $player, User $author, string $content): PlayerNote;
}
