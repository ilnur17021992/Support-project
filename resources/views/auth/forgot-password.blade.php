<x-guest-layout>
    <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
        <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Войти</a>
        <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 dark:text-gray-500 underline">Регистрация</a>
    </div>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ 'Забыли пароль? Без проблем. Просто введите свой адрес электронной почты, и мы вышлем вам ссылку для сброса пароля, которая позволит вам задать новый пароль.' }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ 'Отправить' }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
