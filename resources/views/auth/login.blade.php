<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gradient mb-2">{{ __('Bienvenido') }}</h2>
        <p class="text-gray-600">Accede a tu cuenta para continuar</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-sm font-semibold text-gray-700 mb-2" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="tu@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

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

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-uts-600 shadow-sm focus:ring-uts-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Recuérdame') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-uts-600 hover:text-uts-700 font-medium transition" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <!-- Botones -->
        <div class="flex flex-col gap-3 mt-6 pt-4">
            <x-primary-button class="w-full justify-center">
                {{ __('Iniciar sesión') }}
            </x-primary-button>

            <p class="text-center text-sm text-gray-600">
                ¿No tienes cuenta? 
                <a href="{{ route('register') }}" class="text-uts-600 hover:text-uts-700 font-semibold transition">
                    {{ __('Regístrate aquí') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
