<nav x-data="{ open: false, adminOpen: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
<!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    @if(auth()->user()->role === 'client')
                        <x-nav-link :href="route('client.dashboard')" :active="request()->routeIs('client.dashboard') || request()->routeIs('client.catalog.rentals*')">
                            Каталог для аренды
                        </x-nav-link>
                        <x-nav-link :href="route('client.catalog.test-drives')" :active="request()->routeIs('client.catalog.test-drives*')">
                            Каталог для тест-драйва
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Дашборд
                        </x-nav-link>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin','manager'], true))
                    <x-nav-link :href="route('manager.catalog.rentals')" :active="request()->routeIs('manager.catalog.rentals')">
                        Каталог аренды
                    </x-nav-link>
                    <x-nav-link :href="route('manager.catalog.test-drives')" :active="request()->routeIs('manager.catalog.test-drives')">
                        Каталог тест-драйва
                    </x-nav-link>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin','manager'], true))
                    <x-nav-link :href="route('manager.cars.index')" :active="request()->routeIs('manager.cars.*')">
                        Автомобили
                    </x-nav-link>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin','manager'], true))
                    <x-nav-link :href="route('manager.clients.index')" :active="request()->routeIs('manager.clients.*')">
                        Клиенты
                    </x-nav-link>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin','manager'], true))
                    <x-nav-link :href="route('manager.rentals.index')" :active="request()->routeIs('manager.rentals.*')">
                        Аренды
                    </x-nav-link>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin','manager'], true))
                    <x-nav-link :href="route('manager.test-drives.index')" :active="request()->routeIs('manager.test-drives.*')">
                        Тест-драйвы
                    </x-nav-link>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin','manager'], true))
                    <x-nav-link :href="route('manager.calendar.index')" :active="request()->routeIs('manager.calendar.*')">
                        Календарь
                    </x-nav-link>
                    @endif

                    @if(auth()->user()->role === 'admin')
                    @php
                    $adminActive = request()->routeIs('admin.*');

                    $adminTriggerClasses = $adminActive
                    ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                    : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out';
                    @endphp

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button type="button"
                                @click="open = !open"
                                class="{{ $adminTriggerClasses }}">
                            <span>Админка</span>

                            <svg class="ms-1 h-4 w-4 fill-current transition"
                                 :class="open ? 'rotate-180' : ''"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition
                             style="display:none"
                             class="absolute left-0 mt-2 w-56 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 z-50">
                            <div class="py-1">
                                <x-dropdown-link :href="route('admin.users.index')">Пользователи</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.settings.company')">Реквизиты</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.payments.index')">Платежи</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.audit-logs.index')">Лог действий</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.extras.index')">Доп. услуги</x-dropdown-link>
                            </div>
                        </div>
                    </div>
                    @endif






                </div>

            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()->role === 'client')
                <x-responsive-nav-link :href="route('client.dashboard')" :active="request()->routeIs('client.dashboard') || request()->routeIs('client.catalog.rentals*')">
                    Каталог для аренды
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.catalog.test-drives')" :active="request()->routeIs('client.catalog.test-drives*')">
                    Каталог для тест-драйва
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    Дашборд
                </x-responsive-nav-link>
            @endif

            @if(in_array(auth()->user()->role, ['admin','manager'], true))
            <x-responsive-nav-link :href="route('manager.catalog.rentals')" :active="request()->routeIs('manager.catalog.rentals')">
                Каталог аренды
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('manager.catalog.test-drives')" :active="request()->routeIs('manager.catalog.test-drives')">
                Каталог тест-драйва
            </x-responsive-nav-link>
            @endif

            @if(in_array(auth()->user()->role, ['admin','manager'], true))
            <x-responsive-nav-link :href="route('manager.cars.index')" :active="request()->routeIs('manager.cars.*')">
                Автомобили
            </x-responsive-nav-link>
            @endif

            @if(in_array(auth()->user()->role, ['admin','manager'], true))
            <x-nav-link :href="route('manager.clients.index')" :active="request()->routeIs('manager.clients.*')">
                Клиенты
            </x-nav-link>
            @endif

            @if(in_array(auth()->user()->role, ['admin','manager'], true))
            <x-nav-link :href="route('manager.rentals.index')" :active="request()->routeIs('manager.rentals.*')">
                Аренды
            </x-nav-link>
            @endif

            @if(in_array(auth()->user()->role, ['admin','manager'], true))
            <x-nav-link :href="route('manager.test-drives.index')" :active="request()->routeIs('manager.test-drives.*')">
                Тест-драйвы
            </x-nav-link>
            @endif

            @if(in_array(auth()->user()->role, ['admin','manager'], true))
            <x-nav-link :href="route('manager.calendar.index')" :active="request()->routeIs('manager.calendar.*')">
                Календарь
            </x-nav-link>
            @endif

            @if(auth()->user()->role === 'admin')
            <div class="pt-1">
                <button type="button"
                        @click="adminOpen = !adminOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <span>Админка</span>
                    <svg class="h-4 w-4 fill-current transition" :class="adminOpen ? 'rotate-180' : ''"
                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div x-show="adminOpen" x-collapse class="pl-2 border-l border-gray-200 ml-4 space-y-1">
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                        Пользователи
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.settings.company')" :active="request()->routeIs('admin.settings.company')">
                        Реквизиты
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.payments.index')" :active="request()->routeIs('admin.payments.*')">
                        Платежи
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">
                        Лог действий
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.extras.index')" :active="request()->routeIs('admin.extras.*')">
                        Доп. услуги
                    </x-responsive-nav-link>
                </div>
            </div>
            @endif




        </div>


        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
