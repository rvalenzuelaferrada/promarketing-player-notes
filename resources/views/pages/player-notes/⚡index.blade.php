<?php

use App\Models\PlayerNote;
use App\Models\User;
use App\Repositories\Contracts\PlayerNoteRepositoryInterface;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    /**
     * The id of the player whose notes are being viewed.
     */
    public ?int $selectedPlayerId = null;

    /**
     * The content of the note being created.
     */
    #[Validate('required|string|max:1000')]
    public string $content = '';

    /**
     * The users that can be selected as the note subject.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function players(): Collection
    {
        return User::query()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * The currently selected player, if any.
     */
    #[Computed]
    public function selectedPlayer(): ?User
    {
        return $this->selectedPlayerId === null
            ? null
            : User::find($this->selectedPlayerId);
    }

    /**
     * The notes written about the selected player, newest first.
     *
     * @return Collection<int, PlayerNote>
     */
    #[Computed]
    public function notes(): Collection
    {
        $player = $this->selectedPlayer;

        return $player === null
            ? new Collection
            : app(PlayerNoteRepositoryInterface::class)->getForPlayer($player);
    }

    /**
     * Store a new note for the selected player and refresh the list.
     */
    public function save(PlayerNoteRepositoryInterface $repository): void
    {
        $this->authorize('add-player-notes');

        $player = $this->selectedPlayer;

        if ($player === null) {
            $this->addError('selectedPlayerId', 'Please select a player first.');

            return;
        }

        $validated = $this->validate();

        /** @var User $author */
        $author = auth()->user();

        $repository->create($player, $author, $validated['content']);

        $this->reset('content');

        unset($this->notes);
    }
};
?>

<div class="mx-auto max-w-3xl p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Player Notes — Support panel</h1>
            <p class="mt-1 text-sm text-gray-500">
                Signed in as {{ auth()->user()->name }}
                @can('add-player-notes')
                    <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Can add notes</span>
                @else
                    <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Read-only</span>
                @endcan
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Sign out</button>
        </form>
    </div>

    {{-- Player selector --}}
    <div class="mt-6">
        <label for="player" class="block text-sm font-medium text-gray-700">Player</label>
        <select
            id="player"
            wire:model.live="selectedPlayerId"
            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option value="">— Select a player —</option>
            @foreach ($this->players as $player)
                <option value="{{ $player->id }}">{{ $player->name }}</option>
            @endforeach
        </select>
        @error('selectedPlayerId')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($this->selectedPlayer)
        {{-- Add note form (only for users with permission) --}}
        @can('add-player-notes')
            <form wire:submit="save" class="mt-6 space-y-2">
                <label for="content" class="block text-sm font-medium text-gray-700">Add a note</label>
                <textarea
                    id="content"
                    wire:model="content"
                    rows="3"
                    maxlength="1000"
                    class="block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Write an internal note about this player..."
                ></textarea>
                @error('content')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button
                    type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                >
                    Save note
                </button>
            </form>
        @endcan

        {{-- Notes list --}}
        <table class="mt-8 w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-500">
                    <th class="py-2 pr-4 font-medium">Date</th>
                    <th class="py-2 pr-4 font-medium">Author</th>
                    <th class="py-2 font-medium">Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->notes as $note)
                    <tr wire:key="note-{{ $note->id }}" class="border-b border-gray-100 align-top">
                        <td class="py-2 pr-4 whitespace-nowrap text-gray-500">
                            {{ $note->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="py-2 pr-4 whitespace-nowrap text-gray-700">{{ $note->author->name }}</td>
                        <td class="py-2 text-gray-900">{{ $note->content }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-4 text-center text-gray-400">No notes yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <p class="mt-8 text-center text-sm text-gray-400">Select a player to view their notes.</p>
    @endif
</div>
