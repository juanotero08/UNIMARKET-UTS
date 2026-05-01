<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gradient mb-2">{{ __('¿Olvidaste tu contraseña?') }}</h2>
        <p class="text-gray-600 text-sm">No hay problema. Solo indícanos tu correo y te enviaremos un enlace para que puedas restablecer tu contraseña.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-sm font-semibold text-gray-700 mb-2" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="tu@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-3 mt-6 pt-4">
            <x-primary-button class="w-full justify-center">
                {{ __('Enviar enlace de recuperación') }}
            </x-primary-button>

            <p class="text-center text-sm text-gray-600">
                <a href="{{ route('login') }}" class="text-uts-600 hover:text-uts-700 font-semibold transition">
                    {{ __('Volver al login') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
