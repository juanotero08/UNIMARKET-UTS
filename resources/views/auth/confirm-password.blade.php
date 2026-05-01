<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gradient mb-2">{{ __('Confirmar Identidad') }}</h2>
        <p class="text-gray-600 text-sm">Esta es un área segura. Por favor confirma tu contraseña antes de continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="text-sm font-semibold text-gray-700 mb-2" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center mt-6">
            {{ __('Confirmar') }}
        </x-primary-button>
    </form>
</x-guest-layout>
