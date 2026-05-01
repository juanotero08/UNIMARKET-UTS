<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gradient mb-2">{{ __('Restablecer Contraseña') }}</h2>
        <p class="text-gray-600 text-sm">Ingresa tu correo y una nueva contraseña</p>
    </div>
    
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-sm font-semibold text-gray-700 mb-2" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" placeholder="tu@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Nueva Contraseña')" class="text-sm font-semibold text-gray-700 mb-2" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="text-sm font-semibold text-gray-700 mb-2" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" 
                                placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center mt-6">
            {{ __('Restablecer Contraseña') }}
        </x-primary-button>
    </form>
</x-guest-layout>
