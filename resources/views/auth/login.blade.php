<x-layouts.app title="Sign in — Player Notes">
    <div class="mx-auto max-w-2xl p-6">
        <h1 class="text-2xl font-semibold text-gray-900">Player Notes — Support sign in</h1>
        <p class="mt-1 text-sm text-gray-500">
            Every demo account uses the password <code class="rounded bg-gray-200 px-1">password</code>.
        </p>

        {{-- Manual login form --}}
        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-3 rounded-lg border border-gray-200 bg-white p-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" value="password" required
                    class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            @error('email')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <button type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                Sign in
            </button>
        </form>

        {{-- One-click demo accounts --}}
        <h2 class="mt-8 text-sm font-semibold tracking-wide text-gray-500 uppercase">Demo accounts</h2>
        <ul class="mt-2 divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
            @foreach ($demoUsers as $demoUser)
                <li class="flex items-center justify-between gap-4 p-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-900">{{ $demoUser->name }}</p>
                        <p class="truncate text-xs text-gray-500">{{ $demoUser->email }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($demoUser->can_add_player_notes)
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Can add notes</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Read-only</span>
                        @endif
                        <form method="POST" action="{{ route('login.store') }}">
                            @csrf
                            <input type="hidden" name="email" value="{{ $demoUser->email }}">
                            <input type="hidden" name="password" value="password">
                            <button type="submit"
                                class="rounded-md border border-indigo-200 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-50">
                                Sign in
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</x-layouts.app>
