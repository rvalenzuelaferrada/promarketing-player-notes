<?php

namespace Database\Seeders;

use App\Models\PlayerNote;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Every user shares the password "password" (see UserFactory).
     */
    public function run(): void
    {
        $noteTakers = $this->createSupportAgentsWithPermission();
        $this->createSupportAgentsWithoutPermission();
        $players = $this->createPlayers();

        $this->createNotesForPlayers($players, $noteTakers);
    }

    /**
     * Support agents allowed to add player notes.
     *
     * @return Collection<int, User>
     */
    protected function createSupportAgentsWithPermission(): Collection
    {
        $namedAgent = User::factory()->canAddPlayerNotes()->create([
            'name' => 'Alice Agent',
            'email' => 'alice@support.test',
        ]);

        return User::factory()
            ->count(4)
            ->canAddPlayerNotes()
            ->create()
            ->prepend($namedAgent);
    }

    /**
     * Support users without permission to add notes (read-only).
     *
     * @return Collection<int, User>
     */
    protected function createSupportAgentsWithoutPermission(): Collection
    {
        $namedViewer = User::factory()->create([
            'name' => 'Bob Viewer',
            'email' => 'bob@support.test',
        ]);

        return User::factory()
            ->count(4)
            ->create()
            ->prepend($namedViewer);
    }

    /**
     * Players that notes are written about.
     *
     * @return Collection<int, User>
     */
    protected function createPlayers(): Collection
    {
        return User::factory()->count(10)->create();
    }

    /**
     * Seed a handful of notes per player, authored by permitted agents.
     *
     * @param  Collection<int, User>  $players
     * @param  Collection<int, User>  $noteTakers
     */
    protected function createNotesForPlayers(Collection $players, Collection $noteTakers): void
    {
        $players->each(function (User $player) use ($noteTakers): void {
            PlayerNote::factory()
                ->count(random_int(1, 4))
                ->for($player, 'player')
                ->state(fn (): array => ['author_id' => $noteTakers->random()->getKey()])
                ->create();
        });
    }
}
