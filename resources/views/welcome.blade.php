<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-100">
    <!-- Navbar -->
    <nav class="navbar bg-base-100 shadow-sm">
        <div class="navbar-start">
            <a class="btn btn-text text-xl" href="/">
                <span class="icon-[tabler--brand-laravel] text-primary size-6"></span>
                Laravel + FlyonUI
            </a>
        </div>
        <div class="navbar-end gap-2">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-base-content mb-6">
                Welcome to Laravel 12 with FlyonUI
            </h1>
            <p class="text-lg text-base-content/70 mb-8 max-w-2xl mx-auto">
                Build beautiful, responsive web applications with Laravel and FlyonUI Pro components.
                Get started by exploring the components and blocks available.
            </p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="https://laravel.com/docs" target="_blank" class="btn btn-primary">
                    <span class="icon-[tabler--book] size-5"></span>
                    Laravel Docs
                </a>
                <a href="https://flyonui.com/docs" target="_blank" class="btn btn-outline btn-secondary">
                    <span class="icon-[tabler--layout] size-5"></span>
                    FlyonUI Docs
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 px-4 bg-base-200">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Features</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Feature Card 1 -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                            <span class="icon-[tabler--rocket] size-6 text-primary"></span>
                        </div>
                        <h3 class="card-title">Laravel 12</h3>
                        <p class="text-base-content/70">
                            The latest version of Laravel with improved performance and new features.
                        </p>
                    </div>
                </div>

                <!-- Feature Card 2 -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <div class="size-12 rounded-full bg-secondary/10 flex items-center justify-center mb-4">
                            <span class="icon-[tabler--palette] size-6 text-secondary"></span>
                        </div>
                        <h3 class="card-title">FlyonUI Pro</h3>
                        <p class="text-base-content/70">
                            Beautiful pre-built components and blocks for rapid UI development.
                        </p>
                    </div>
                </div>

                <!-- Feature Card 3 -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <div class="size-12 rounded-full bg-accent/10 flex items-center justify-center mb-4">
                            <span class="icon-[tabler--device-mobile] size-6 text-accent"></span>
                        </div>
                        <h3 class="card-title">Responsive Design</h3>
                        <p class="text-base-content/70">
                            Fully responsive components that work on all screen sizes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sample Components Section -->
    <section class="py-16 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Sample Components</h2>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Buttons -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Buttons</h3>
                        <div class="flex flex-wrap gap-2">
                            <button class="btn btn-primary">Primary</button>
                            <button class="btn btn-secondary">Secondary</button>
                            <button class="btn btn-accent">Accent</button>
                            <button class="btn btn-outline">Outline</button>
                        </div>
                    </div>
                </div>

                <!-- Badges -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Badges</h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="badge badge-primary">Primary</span>
                            <span class="badge badge-secondary">Secondary</span>
                            <span class="badge badge-accent">Accent</span>
                            <span class="badge badge-success">Success</span>
                            <span class="badge badge-warning">Warning</span>
                            <span class="badge badge-error">Error</span>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Alerts</h3>
                        <div class="space-y-2">
                            <div class="alert alert-info">
                                <span class="icon-[tabler--info-circle] size-5"></span>
                                <span>This is an info alert.</span>
                            </div>
                            <div class="alert alert-success">
                                <span class="icon-[tabler--check] size-5"></span>
                                <span>This is a success alert.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Accordion</h3>
                        <div class="accordion divide-y divide-base-200" id="sample-accordion">
                            <div class="accordion-item">
                                <button class="accordion-toggle inline-flex items-center gap-x-4 px-5 py-4 text-start w-full" aria-controls="accordion-1" aria-expanded="false">
                                    <span class="icon-[tabler--plus] accordion-item-active:hidden size-4 shrink-0"></span>
                                    <span class="icon-[tabler--minus] accordion-item-active:block hidden size-4 shrink-0"></span>
                                    What is FlyonUI?
                                </button>
                                <div id="accordion-1" class="accordion-content w-full overflow-hidden transition-[height] duration-300 hidden" role="region">
                                    <div class="px-5 pb-4">
                                        <p class="text-base-content/70">
                                            FlyonUI is a component library built on top of Tailwind CSS with beautiful pre-built components.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <button class="accordion-toggle inline-flex items-center gap-x-4 px-5 py-4 text-start w-full" aria-controls="accordion-2" aria-expanded="false">
                                    <span class="icon-[tabler--plus] accordion-item-active:hidden size-4 shrink-0"></span>
                                    <span class="icon-[tabler--minus] accordion-item-active:block hidden size-4 shrink-0"></span>
                                    Is it free?
                                </button>
                                <div id="accordion-2" class="accordion-content w-full overflow-hidden transition-[height] duration-300 hidden" role="region">
                                    <div class="px-5 pb-4">
                                        <p class="text-base-content/70">
                                            FlyonUI offers both free and pro versions with advanced components.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-base-200 text-base-content p-10">
        <aside>
            <span class="icon-[tabler--brand-laravel] text-primary size-10"></span>
            <p>
                Laravel + FlyonUI
                <br />
                Building beautiful web applications since 2024
            </p>
        </aside>
        <nav>
            <h6 class="footer-title">Documentation</h6>
            <a class="link link-hover" href="https://laravel.com/docs" target="_blank">Laravel Docs</a>
            <a class="link link-hover" href="https://flyonui.com/docs" target="_blank">FlyonUI Docs</a>
            <a class="link link-hover" href="https://tailwindcss.com/docs" target="_blank">Tailwind CSS</a>
        </nav>
        <nav>
            <h6 class="footer-title">Resources</h6>
            <a class="link link-hover" href="https://flyonui.com/blocks" target="_blank">FlyonUI Blocks</a>
            <a class="link link-hover" href="https://flyonui.com/templates" target="_blank">Templates</a>
        </nav>
    </footer>

    <!-- FlyonUI JavaScript -->
    <script src="{{ asset('assets/js/flyonui.js') }}"></script>
</body>
</html>
