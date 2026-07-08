<?php

use App\Models\PlayerNote;
use App\Models\User;
use App\Repositories\Contracts\PlayerNoteRepositoryInterface;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('components.layouts.app')] class extends Component
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
     * Spanish validation messages for the note field.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'content.required' => 'La nota no puede estar vacía.',
            'content.max' => 'La nota no puede superar los 1000 caracteres.',
        ];
    }

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
     * The notes to display: the selected player's notes, or the latest
     * notes across all players when no player is selected.
     *
     * @return Collection<int, PlayerNote>
     */
    #[Computed]
    public function notes(): Collection
    {
        $repository = app(PlayerNoteRepositoryInterface::class);
        $player = $this->selectedPlayer;

        return $player === null
            ? $repository->getLatest()
            : $repository->getForPlayer($player);
    }

    /**
     * Store a new note for the selected player and refresh the list.
     */
    public function save(PlayerNoteRepositoryInterface $repository): void
    {
        $this->authorize('add-player-notes');

        $player = $this->selectedPlayer;

        if ($player === null) {
            $this->addError('selectedPlayerId', 'Selecciona un jugador primero.');

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
            <h1 class="text-xl font-semibold text-gray-900">Notas de Jugadores — Panel de soporte</h1>
            <p class="mt-1 text-sm text-gray-500">
                Sesión iniciada como {{ auth()->user()->name }}
                @can('add-player-notes')
                    <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Puede agregar notas</span>
                @else
                    <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Solo lectura</span>
                @endcan
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Cerrar sesión</button>
        </form>
    </div>

    {{-- Player selector --}}
    <div class="mt-6">
        <label for="player" class="block text-sm font-medium text-gray-700">Jugador</label>
        <select
            id="player"
            wire:model.live="selectedPlayerId"
            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option value="">— Selecciona un jugador —</option>
            @foreach ($this->players as $player)
                <option value="{{ $player->id }}">{{ $player->name }}</option>
            @endforeach
        </select>
        @error('selectedPlayerId')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Add note form (requires permission; prompt to pick a player otherwise) --}}
    @can('add-player-notes')
        @if ($this->selectedPlayer)
            <form
                wire:submit="save"
                x-data="{ length: 0 }"
                x-effect="length = ($wire.content ?? '').length"
                class="mt-6 space-y-2"
            >
                <label for="content" class="block text-sm font-medium text-gray-700">Agregar una nota</label>
                <textarea
                    id="content"
                    wire:model="content"
                    rows="3"
                    maxlength="1000"
                    class="block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Escribe una nota interna sobre este jugador..."
                ></textarea>

                <div class="flex items-center justify-between">
                    <div>
                        @error('content')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <span class="text-xs text-gray-400">
                        <span x-text="length">0</span>/1000
                    </span>
                </div>

                <button
                    type="submit"
                    x-bind:disabled="length === 0"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-indigo-600"
                >
                    Guardar nota
                </button>
            </form>
        @else
            <p class="mt-6 rounded-md border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500">
                Selecciona un jugador para agregar una nota.
            </p>
        @endif
    @endcan

    {{-- Notes list: latest across all players, or the selected player's notes --}}
    <h2 class="mt-8 text-sm font-semibold text-gray-700">
        {{ $this->selectedPlayer ? 'Notas de '.$this->selectedPlayer->name : 'Últimas notas' }}
    </h2>

    <table class="mt-2 w-full text-left text-sm">
        <thead>
            <tr class="border-b border-gray-200 text-gray-500">
                <th class="py-2 pr-4 font-medium">Fecha</th>
                @unless ($this->selectedPlayer)
                    <th class="py-2 pr-4 font-medium">Jugador</th>
                @endunless
                <th class="py-2 pr-4 font-medium">Autor</th>
                <th class="py-2 font-medium">Nota</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->notes as $note)
                <tr wire:key="note-{{ $note->id }}" class="border-b border-gray-100 align-top">
                    <td class="py-2 pr-4 whitespace-nowrap text-gray-500">
                        {{ $note->created_at->format('Y-m-d H:i') }}
                    </td>
                    @unless ($this->selectedPlayer)
                        <td class="py-2 pr-4 whitespace-nowrap text-gray-700">{{ $note->player->name }}</td>
                    @endunless
                    <td class="py-2 pr-4 whitespace-nowrap text-gray-700">{{ $note->author->name }}</td>
                    <td class="py-2 text-gray-900">{{ $note->content }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-4 text-center text-gray-400">Aún no hay notas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
