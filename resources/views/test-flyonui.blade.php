<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FlyonUI Theme Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-100">

    <!-- Navbar -->
    <nav class="navbar bg-base-100 shadow-sm sticky top-0 z-50">
        <div class="navbar-start">
            <a class="btn btn-text text-xl font-bold" href="/">
                <span class="icon-[tabler--brand-laravel] text-error size-8"></span>
                FlyonUI Demo
            </a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal gap-2">
                <li><a class="menu-item">Home</a></li>
                <li><a class="menu-item">Features</a></li>
                <li><a class="menu-item">Pricing</a></li>
                <li><a class="menu-item">Contact</a></li>
            </ul>
        </div>
        <div class="navbar-end gap-2">
            <button class="btn btn-ghost">Login</button>
            <button class="btn btn-primary">Get Started</button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero min-h-[80vh] bg-gradient-to-br from-primary/10 via-base-100 to-secondary/10">
        <div class="hero-content text-center py-20">
            <div class="max-w-3xl">
                <div class="badge badge-primary badge-soft mb-4">
                    <span class="icon-[tabler--sparkles] size-4 mr-1"></span>
                    FlyonUI Pro Installed
                </div>
                <h1 class="text-5xl md:text-6xl font-bold text-base-content mb-6">
                    Build Beautiful Apps with
                    <span class="text-primary">Laravel</span> &
                    <span class="text-secondary">FlyonUI</span>
                </h1>
                <p class="text-xl text-base-content/70 mb-8 max-w-2xl mx-auto">
                    Your FlyonUI theme is successfully installed! Start building stunning, responsive web applications with pre-built components.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <button class="btn btn-primary btn-lg">
                        <span class="icon-[tabler--rocket] size-5"></span>
                        Get Started
                    </button>
                    <button class="btn btn-outline btn-lg">
                        <span class="icon-[tabler--player-play] size-5"></span>
                        Watch Demo
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12 bg-base-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="stat bg-base-100 rounded-box shadow-sm text-center">
                    <div class="stat-value text-primary">50+</div>
                    <div class="stat-desc">Components</div>
                </div>
                <div class="stat bg-base-100 rounded-box shadow-sm text-center">
                    <div class="stat-value text-secondary">100+</div>
                    <div class="stat-desc">Blocks</div>
                </div>
                <div class="stat bg-base-100 rounded-box shadow-sm text-center">
                    <div class="stat-value text-accent">20+</div>
                    <div class="stat-desc">Themes</div>
                </div>
                <div class="stat bg-base-100 rounded-box shadow-sm text-center">
                    <div class="stat-value text-success">Free</div>
                    <div class="stat-desc">Updates</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Powerful Features</h2>
                <p class="text-base-content/70 text-lg max-w-2xl mx-auto">
                    Everything you need to build modern web applications
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow">
                    <div class="card-body items-center text-center">
                        <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                            <span class="icon-[tabler--palette] size-8 text-primary"></span>
                        </div>
                        <h3 class="card-title">Beautiful Design</h3>
                        <p class="text-base-content/70">
                            Professionally crafted components with attention to every detail.
                        </p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow">
                    <div class="card-body items-center text-center">
                        <div class="size-16 rounded-full bg-secondary/10 flex items-center justify-center mb-4">
                            <span class="icon-[tabler--device-mobile] size-8 text-secondary"></span>
                        </div>
                        <h3 class="card-title">Fully Responsive</h3>
                        <p class="text-base-content/70">
                            Works perfectly on all devices from mobile to desktop.
                        </p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow">
                    <div class="card-body items-center text-center">
                        <div class="size-16 rounded-full bg-accent/10 flex items-center justify-center mb-4">
                            <span class="icon-[tabler--bolt] size-8 text-accent"></span>
                        </div>
                        <h3 class="card-title">Lightning Fast</h3>
                        <p class="text-base-content/70">
                            Optimized for performance with minimal bundle size.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Components Showcase -->
    <section class="py-20 px-4 bg-base-200">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Component Showcase</h2>
                <p class="text-base-content/70 text-lg">See FlyonUI components in action</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Buttons Card -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--click] size-5 text-primary"></span>
                            Buttons
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <button class="btn btn-primary btn-sm">Primary</button>
                            <button class="btn btn-secondary btn-sm">Secondary</button>
                            <button class="btn btn-accent btn-sm">Accent</button>
                            <button class="btn btn-outline btn-sm">Outline</button>
                            <button class="btn btn-ghost btn-sm">Ghost</button>
                        </div>
                    </div>
                </div>

                <!-- Badges Card -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--tag] size-5 text-secondary"></span>
                            Badges
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="badge badge-primary">Primary</span>
                            <span class="badge badge-secondary">Secondary</span>
                            <span class="badge badge-success">Success</span>
                            <span class="badge badge-warning">Warning</span>
                            <span class="badge badge-error">Error</span>
                            <span class="badge badge-info">Info</span>
                        </div>
                    </div>
                </div>

                <!-- Alerts Card -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--alert-circle] size-5 text-accent"></span>
                            Alerts
                        </h3>
                        <div class="space-y-2">
                            <div class="alert alert-success py-2">
                                <span class="icon-[tabler--check] size-4"></span>
                                <span class="text-sm">Success message!</span>
                            </div>
                            <div class="alert alert-error py-2">
                                <span class="icon-[tabler--x] size-4"></span>
                                <span class="text-sm">Error message!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Card -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--loader] size-5 text-primary"></span>
                            Progress
                        </h3>
                        <div class="space-y-3">
                            <progress class="progress progress-primary" value="70" max="100"></progress>
                            <progress class="progress progress-secondary" value="50" max="100"></progress>
                            <progress class="progress progress-accent" value="90" max="100"></progress>
                        </div>
                    </div>
                </div>

                <!-- Toggle Card -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--toggle-right] size-5 text-secondary"></span>
                            Toggles
                        </h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" class="toggle toggle-primary" checked />
                                <span class="text-sm">Enable notifications</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" class="toggle toggle-secondary" />
                                <span class="text-sm">Dark mode</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Input Card -->
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--forms] size-5 text-accent"></span>
                            Inputs
                        </h3>
                        <div class="space-y-2">
                            <input type="text" placeholder="Text input" class="input input-bordered w-full" />
                            <select class="select select-bordered w-full">
                                <option>Select option</option>
                                <option>Option 1</option>
                                <option>Option 2</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Accordion Section -->
    <section class="py-20 px-4">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">FAQ</h2>
                <p class="text-base-content/70">Frequently asked questions</p>
            </div>

            <div class="accordion divide-y divide-base-200 bg-base-100 rounded-box shadow-md" id="faq-accordion">
                <div class="accordion-item" id="faq-1">
                    <button class="accordion-toggle inline-flex items-center justify-between gap-x-4 px-6 py-4 text-start w-full font-medium" aria-controls="faq-content-1" aria-expanded="false">
                        What is FlyonUI?
                        <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 size-5 transition-transform"></span>
                    </button>
                    <div id="faq-content-1" class="accordion-content w-full overflow-hidden transition-[height] duration-300 hidden" role="region" aria-labelledby="faq-1">
                        <div class="px-6 pb-4">
                            <p class="text-base-content/70">
                                FlyonUI is a beautiful component library built on top of Tailwind CSS. It provides pre-built, customizable components and blocks for rapid UI development.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item" id="faq-2">
                    <button class="accordion-toggle inline-flex items-center justify-between gap-x-4 px-6 py-4 text-start w-full font-medium" aria-controls="faq-content-2" aria-expanded="false">
                        How do I use the MCP Server?
                        <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 size-5 transition-transform"></span>
                    </button>
                    <div id="faq-content-2" class="accordion-content w-full overflow-hidden transition-[height] duration-300 hidden" role="region" aria-labelledby="faq-2">
                        <div class="px-6 pb-4">
                            <p class="text-base-content/70">
                                Use commands like /iui (Inspire UI), /cui (Create UI), and /rui (Refine UI) in your IDE's AI chat to generate and customize components.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item" id="faq-3">
                    <button class="accordion-toggle inline-flex items-center justify-between gap-x-4 px-6 py-4 text-start w-full font-medium" aria-controls="faq-content-3" aria-expanded="false">
                        Is it compatible with Laravel?
                        <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 size-5 transition-transform"></span>
                    </button>
                    <div id="faq-content-3" class="accordion-content w-full overflow-hidden transition-[height] duration-300 hidden" role="region" aria-labelledby="faq-3">
                        <div class="px-6 pb-4">
                            <p class="text-base-content/70">
                                Yes! FlyonUI works perfectly with Laravel and any framework that supports Tailwind CSS, including React, Vue, Next.js, and more.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 bg-gradient-to-r from-primary to-secondary">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold text-white mb-4">Ready to Build Something Amazing?</h2>
            <p class="text-white/80 text-lg mb-8 max-w-2xl mx-auto">
                Start creating beautiful interfaces with FlyonUI and Laravel today.
            </p>
            <div class="flex flex-wrap gap-4 justify-center">
                <button class="btn btn-lg bg-white text-primary hover:bg-white/90">
                    <span class="icon-[tabler--rocket] size-5"></span>
                    Start Building
                </button>
                <button class="btn btn-lg btn-outline border-white text-white hover:bg-white/10">
                    <span class="icon-[tabler--book] size-5"></span>
                    Read Docs
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-base-200 text-base-content p-10">
        <aside>
            <span class="icon-[tabler--brand-laravel] text-error size-12"></span>
            <p class="font-bold text-lg">Laravel + FlyonUI</p>
            <p class="text-base-content/70">Building beautiful web apps since 2024</p>
        </aside>
        <nav>
            <h6 class="footer-title">Product</h6>
            <a class="link link-hover">Features</a>
            <a class="link link-hover">Pricing</a>
            <a class="link link-hover">Components</a>
            <a class="link link-hover">Blocks</a>
        </nav>
        <nav>
            <h6 class="footer-title">Resources</h6>
            <a class="link link-hover">Documentation</a>
            <a class="link link-hover">Tutorials</a>
            <a class="link link-hover">Blog</a>
            <a class="link link-hover">Changelog</a>
        </nav>
        <nav>
            <h6 class="footer-title">Company</h6>
            <a class="link link-hover">About</a>
            <a class="link link-hover">Contact</a>
            <a class="link link-hover">Support</a>
        </nav>
    </footer>

    <!-- Footer Bottom -->
    <div class="bg-base-300 py-4 px-4 text-center text-base-content/70 text-sm">
        <p>&copy; 2024 Laravel + FlyonUI. All rights reserved.</p>
    </div>

    <!-- FlyonUI JavaScript -->
    <script src="{{ asset('assets/js/flyonui.js') }}"></script>
</body>
</html>
