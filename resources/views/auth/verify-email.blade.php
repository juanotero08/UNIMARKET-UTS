<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gradient mb-2">{{ __('Verifica tu Correo') }}</h2>
        <p class="text-gray-600 text-sm">¡Gracias por registrarte! Por favor, verifica tu correo electrónico haciendo clic en el enlace que te enviamos. Si no recibiste el correo, te enviaremos otro.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-uts-600 rounded-lg">
            <p class="text-uts-700 font-medium">{{ __('✓ Se envió un nuevo enlace de verificación al correo que registraste.') }}</p>
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full">
            @csrf
            <button type="submit" class="btn-primary w-full justify-center">
                {{ __('Reenviar correo de verificación') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="btn-secondary w-full justify-center">
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>
