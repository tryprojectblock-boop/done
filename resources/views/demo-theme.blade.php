@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w">
    {{-- Page Header --}}
    <div class="mb-12">
        <h1 class="text-4xl font-bold text-base-content mb-2">NewDone Theme Demo</h1>
        <p class="text-base-content/70">A comprehensive showcase of all FlyonUI components used in this application.</p>
    </div>

    {{-- Table of Contents --}}
    <div class="card bg-base-200 mb-12">
        <div class="card-body">
            <h2 class="card-title">Table of Contents</h2>
            <div class="flex flex-wrap gap-2">
                <a href="#badges" class="badge badge-primary badge-outline">Badges</a>
                <a href="#avatars" class="badge badge-primary badge-outline">Avatars</a>
                <a href="#accordion" class="badge badge-primary badge-outline">Accordion</a>
                <a href="#tabs" class="badge badge-primary badge-outline">Tabs</a>
                <a href="#dropdowns" class="badge badge-primary badge-outline">Dropdowns</a>
                <a href="#modals" class="badge badge-primary badge-outline">Modals</a>
                <a href="#advanced-select" class="badge badge-primary badge-outline">Advanced Select</a>
                <a href="#password" class="badge badge-primary badge-outline">Password Components</a>
                <a href="#datetime" class="badge badge-primary badge-outline">DateTime Picker</a>
            </div>
        </div>
    </div>

    {{-- ============================================================
         BADGES SECTION
         ============================================================ --}}
    <section id="badges" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">Badges</h2>
        </div>

        {{-- Solid Badges --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Solid Badges</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="badge">Default</span>
                    <span class="badge badge-primary">Primary</span>
                    <span class="badge badge-secondary">Secondary</span>
                    <span class="badge badge-accent">Accent</span>
                    <span class="badge badge-info">Info</span>
                    <span class="badge badge-success">Success</span>
                    <span class="badge badge-warning">Warning</span>
                    <span class="badge badge-error">Error</span>
                </div>
            </div>
        </div>

        {{-- Soft Badges --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Soft Badges</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="badge badge-soft">Default</span>
                    <span class="badge badge-soft badge-primary">Primary</span>
                    <span class="badge badge-soft badge-secondary">Secondary</span>
                    <span class="badge badge-soft badge-accent">Accent</span>
                    <span class="badge badge-soft badge-info">Info</span>
                    <span class="badge badge-soft badge-success">Success</span>
                    <span class="badge badge-soft badge-warning">Warning</span>
                    <span class="badge badge-soft badge-error">Error</span>
                </div>
            </div>
        </div>

        {{-- Outline Badges --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Outline Badges</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="badge badge-outline">Default</span>
                    <span class="badge badge-outline badge-primary">Primary</span>
                    <span class="badge badge-outline badge-secondary">Secondary</span>
                    <span class="badge badge-outline badge-accent">Accent</span>
                    <span class="badge badge-outline badge-info">Info</span>
                    <span class="badge badge-outline badge-success">Success</span>
                    <span class="badge badge-outline badge-warning">Warning</span>
                    <span class="badge badge-outline badge-error">Error</span>
                </div>
            </div>
        </div>

        {{-- Badge Sizes --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Badge Sizes</h3>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-primary badge-xs">Extra Small</span>
                    <span class="badge badge-primary badge-sm">Small</span>
                    <span class="badge badge-primary">Default</span>
                    <span class="badge badge-primary badge-lg">Large</span>
                    <span class="badge badge-primary badge-xl">Extra Large</span>
                </div>
            </div>
        </div>

        {{-- Pill Badges --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Pill & Icon Badges</h3>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-primary rounded-full">Pill Badge</span>
                    <span class="badge badge-secondary rounded-full">Another Pill</span>
                    <span class="badge badge-info size-6 p-0 rounded-full">
                        <span class="icon-[tabler--user] size-4"></span>
                    </span>
                    <span class="badge badge-success size-6 p-0 rounded-full">
                        <span class="icon-[tabler--check] size-4"></span>
                    </span>
                    <span class="badge badge-error size-6 p-0 rounded-full">
                        <span class="icon-[tabler--x] size-4"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Dismissible Badges --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg">Dismissible Badges</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="badge badge-primary badge-lg gap-1" id="dismiss-badge-1">
                        Dismissible
                        <button class="btn btn-circle btn-ghost btn-xs" data-remove-element="#dismiss-badge-1" aria-label="Remove badge">
                            <span class="icon-[tabler--x] size-3"></span>
                        </button>
                    </span>
                    <span class="badge badge-secondary badge-lg gap-1" id="dismiss-badge-2">
                        Click X to remove
                        <button class="btn btn-circle btn-ghost btn-xs" data-remove-element="#dismiss-badge-2" aria-label="Remove badge">
                            <span class="icon-[tabler--x] size-3"></span>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         AVATARS SECTION
         ============================================================ --}}
    <section id="avatars" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">Avatars</h2>
        </div>

        {{-- Circular Avatars --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Circular Avatars (Various Sizes)</h3>
                <div class="flex flex-wrap items-end gap-4">
                    <div class="avatar">
                        <div class="size-6 rounded-full">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-8 rounded-full">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-2.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-10 rounded-full">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-3.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-12 rounded-full">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-4.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-16 rounded-full">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-5.png" alt="avatar" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rounded Avatars --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Rounded Avatars</h3>
                <div class="flex flex-wrap items-end gap-4">
                    <div class="avatar">
                        <div class="size-10 rounded-md">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-12 rounded-lg">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-2.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-14 rounded-xl">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-3.png" alt="avatar" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Placeholder Avatars --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Placeholder Avatars (Initials)</h3>
                <div class="flex flex-wrap items-end gap-4">
                    <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content size-10 rounded-full">
                            <span class="text-md uppercase">JD</span>
                        </div>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content size-12 rounded-full">
                            <span class="text-lg uppercase">AB</span>
                        </div>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-secondary text-secondary-content size-14 rounded-full">
                            <span class="text-xl uppercase">XY</span>
                        </div>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-accent text-accent-content size-16 rounded-full">
                            <span class="text-2xl uppercase">MK</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Indicators --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Status Indicators</h3>
                <div class="flex flex-wrap items-center gap-6">
                    <div class="flex flex-col items-center gap-2">
                        <div class="avatar avatar-online">
                            <div class="size-12 rounded-full">
                                <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png" alt="avatar" />
                            </div>
                        </div>
                        <span class="text-sm text-base-content/70">Online</span>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="avatar avatar-offline">
                            <div class="size-12 rounded-full">
                                <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-2.png" alt="avatar" />
                            </div>
                        </div>
                        <span class="text-sm text-base-content/70">Offline</span>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="avatar avatar-away">
                            <div class="size-12 rounded-full">
                                <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-3.png" alt="avatar" />
                            </div>
                        </div>
                        <span class="text-sm text-base-content/70">Away</span>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="avatar avatar-busy">
                            <div class="size-12 rounded-full">
                                <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-4.png" alt="avatar" />
                            </div>
                        </div>
                        <span class="text-sm text-base-content/70">Busy</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Avatar Group --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg">Avatar Group</h3>
                <div class="avatar-group -space-x-4">
                    <div class="avatar">
                        <div class="size-12">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-12">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-2.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-12">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-3.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="size-12">
                            <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-4.png" alt="avatar" />
                        </div>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content size-12">
                            <span>+5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         ACCORDION SECTION
         ============================================================ --}}
    <section id="accordion" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">Accordion</h2>
        </div>

        {{-- Basic Accordion --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Basic Accordion</h3>
                <div class="accordion divide-y divide-base-content/10" id="basic-accordion">
                    <div class="accordion-item active" id="basic-heading-one">
                        <button class="accordion-toggle inline-flex w-full items-center justify-between gap-x-3 py-4 text-start font-semibold text-base-content transition-all" aria-controls="basic-collapse-one" aria-expanded="true">
                            What is FlyonUI?
                            <span class="icon-[tabler--plus] accordion-item-active:hidden block size-4 shrink-0"></span>
                            <span class="icon-[tabler--minus] accordion-item-active:block hidden size-4 shrink-0"></span>
                        </button>
                        <div id="basic-collapse-one" class="accordion-content w-full overflow-hidden transition-[height] duration-300" aria-labelledby="basic-heading-one" role="region">
                            <p class="pb-4 text-base-content/80">
                                FlyonUI is a Tailwind CSS component library that provides beautiful, accessible, and customizable components for building modern web applications.
                            </p>
                        </div>
                    </div>
                    <div class="accordion-item" id="basic-heading-two">
                        <button class="accordion-toggle inline-flex w-full items-center justify-between gap-x-3 py-4 text-start font-semibold text-base-content transition-all" aria-controls="basic-collapse-two" aria-expanded="false">
                            How do I install it?
                            <span class="icon-[tabler--plus] accordion-item-active:hidden block size-4 shrink-0"></span>
                            <span class="icon-[tabler--minus] accordion-item-active:block hidden size-4 shrink-0"></span>
                        </button>
                        <div id="basic-collapse-two" class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="basic-heading-two" role="region">
                            <p class="pb-4 text-base-content/80">
                                You can install FlyonUI via npm: <code class="bg-base-200 px-2 py-1 rounded">npm install flyonui</code>. Then import it in your CSS file.
                            </p>
                        </div>
                    </div>
                    <div class="accordion-item" id="basic-heading-three">
                        <button class="accordion-toggle inline-flex w-full items-center justify-between gap-x-3 py-4 text-start font-semibold text-base-content transition-all" aria-controls="basic-collapse-three" aria-expanded="false">
                            Is it accessible?
                            <span class="icon-[tabler--plus] accordion-item-active:hidden block size-4 shrink-0"></span>
                            <span class="icon-[tabler--minus] accordion-item-active:block hidden size-4 shrink-0"></span>
                        </button>
                        <div id="basic-collapse-three" class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="basic-heading-three" role="region">
                            <p class="pb-4 text-base-content/80">
                                Yes! FlyonUI components are built with accessibility in mind, following WAI-ARIA guidelines for interactive elements.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bordered Accordion --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Bordered Accordion</h3>
                <div class="accordion accordion-bordered" id="bordered-accordion">
                    <div class="accordion-item accordion-item-active:border accordion-item-active:rounded-lg active" id="bordered-heading-one">
                        <button class="accordion-toggle inline-flex w-full items-center justify-between gap-x-3 px-4 py-4 text-start font-semibold text-base-content transition-all" aria-controls="bordered-collapse-one" aria-expanded="true">
                            Project Management
                            <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 size-5 shrink-0 transition-transform duration-300"></span>
                        </button>
                        <div id="bordered-collapse-one" class="accordion-content w-full overflow-hidden transition-[height] duration-300" aria-labelledby="bordered-heading-one" role="region">
                            <p class="px-4 pb-4 text-base-content/80">
                                Organize your projects with powerful tools for task management, team collaboration, and progress tracking.
                            </p>
                        </div>
                    </div>
                    <div class="accordion-item accordion-item-active:border accordion-item-active:rounded-lg" id="bordered-heading-two">
                        <button class="accordion-toggle inline-flex w-full items-center justify-between gap-x-3 px-4 py-4 text-start font-semibold text-base-content transition-all" aria-controls="bordered-collapse-two" aria-expanded="false">
                            Team Collaboration
                            <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 size-5 shrink-0 transition-transform duration-300"></span>
                        </button>
                        <div id="bordered-collapse-two" class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="bordered-heading-two" role="region">
                            <p class="px-4 pb-4 text-base-content/80">
                                Work together seamlessly with real-time chat, file sharing, and commenting features.
                            </p>
                        </div>
                    </div>
                    <div class="accordion-item accordion-item-active:border accordion-item-active:rounded-lg" id="bordered-heading-three">
                        <button class="accordion-toggle inline-flex w-full items-center justify-between gap-x-3 px-4 py-4 text-start font-semibold text-base-content transition-all" aria-controls="bordered-collapse-three" aria-expanded="false">
                            Analytics & Reports
                            <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 size-5 shrink-0 transition-transform duration-300"></span>
                        </button>
                        <div id="bordered-collapse-three" class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="bordered-heading-three" role="region">
                            <p class="px-4 pb-4 text-base-content/80">
                                Get insights into your team's productivity with detailed analytics and customizable reports.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shadow Accordion --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg">Shadow Accordion (Always Open)</h3>
                <div class="accordion accordion-shadow space-y-4 *:accordion-item-active:shadow-md" data-accordion-always-open="" id="shadow-accordion">
                    <div class="accordion-item bg-base-100 rounded-lg active" id="shadow-heading-one">
                        <button class="accordion-toggle inline-flex w-full items-center gap-x-4 px-5 py-4 text-start font-semibold text-base-content transition-all" aria-controls="shadow-collapse-one" aria-expanded="true">
                            <span class="icon-[tabler--rocket] size-5 text-primary shrink-0"></span>
                            Getting Started
                            <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 ms-auto size-5 shrink-0 transition-transform duration-300"></span>
                        </button>
                        <div id="shadow-collapse-one" class="accordion-content w-full overflow-hidden transition-[height] duration-300" aria-labelledby="shadow-heading-one" role="region">
                            <p class="px-5 pb-4 text-base-content/80">
                                Learn how to set up your first workspace and invite team members to collaborate on projects.
                            </p>
                        </div>
                    </div>
                    <div class="accordion-item bg-base-100 rounded-lg" id="shadow-heading-two">
                        <button class="accordion-toggle inline-flex w-full items-center gap-x-4 px-5 py-4 text-start font-semibold text-base-content transition-all" aria-controls="shadow-collapse-two" aria-expanded="false">
                            <span class="icon-[tabler--settings] size-5 text-secondary shrink-0"></span>
                            Configuration
                            <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 ms-auto size-5 shrink-0 transition-transform duration-300"></span>
                        </button>
                        <div id="shadow-collapse-two" class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="shadow-heading-two" role="region">
                            <p class="px-5 pb-4 text-base-content/80">
                                Customize your workspace settings, notifications, and integrations to fit your workflow.
                            </p>
                        </div>
                    </div>
                    <div class="accordion-item bg-base-100 rounded-lg" id="shadow-heading-three">
                        <button class="accordion-toggle inline-flex w-full items-center gap-x-4 px-5 py-4 text-start font-semibold text-base-content transition-all" aria-controls="shadow-collapse-three" aria-expanded="false">
                            <span class="icon-[tabler--help] size-5 text-accent shrink-0"></span>
                            FAQ & Support
                            <span class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 ms-auto size-5 shrink-0 transition-transform duration-300"></span>
                        </button>
                        <div id="shadow-collapse-three" class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="shadow-heading-three" role="region">
                            <p class="px-5 pb-4 text-base-content/80">
                                Find answers to common questions or reach out to our support team for help.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         TABS SECTION
         ============================================================ --}}
    <section id="tabs" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">Tabs</h2>
        </div>

        {{-- Bordered Tabs --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Bordered Tabs</h3>
                <nav class="tabs tabs-bordered" aria-label="Bordered Tabs" role="tablist">
                    <button type="button" class="tab active-tab:tab-active active" id="bordered-tab-1" data-tab="#bordered-panel-1" aria-controls="bordered-panel-1" role="tab" aria-selected="true">
                        <span class="icon-[tabler--home] size-4 me-2"></span>
                        Home
                    </button>
                    <button type="button" class="tab active-tab:tab-active" id="bordered-tab-2" data-tab="#bordered-panel-2" aria-controls="bordered-panel-2" role="tab" aria-selected="false">
                        <span class="icon-[tabler--user] size-4 me-2"></span>
                        Profile
                    </button>
                    <button type="button" class="tab active-tab:tab-active" id="bordered-tab-3" data-tab="#bordered-panel-3" aria-controls="bordered-panel-3" role="tab" aria-selected="false">
                        <span class="icon-[tabler--settings] size-4 me-2"></span>
                        Settings
                    </button>
                </nav>
                <div class="mt-4">
                    <div id="bordered-panel-1" role="tabpanel" aria-labelledby="bordered-tab-1">
                        <p class="text-base-content/80">Welcome to the home tab. This is where you'll find your dashboard overview and quick actions.</p>
                    </div>
                    <div id="bordered-panel-2" class="hidden" role="tabpanel" aria-labelledby="bordered-tab-2">
                        <p class="text-base-content/80">Manage your profile information, avatar, and personal preferences here.</p>
                    </div>
                    <div id="bordered-panel-3" class="hidden" role="tabpanel" aria-labelledby="bordered-tab-3">
                        <p class="text-base-content/80">Configure your application settings, notifications, and security options.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lifted Tabs --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Lifted Tabs</h3>
                <nav class="tabs tabs-lifted" aria-label="Lifted Tabs" role="tablist">
                    <button type="button" class="tab active-tab:tab-active active" id="lifted-tab-1" data-tab="#lifted-panel-1" aria-controls="lifted-panel-1" role="tab" aria-selected="true">
                        Overview
                    </button>
                    <button type="button" class="tab active-tab:tab-active" id="lifted-tab-2" data-tab="#lifted-panel-2" aria-controls="lifted-panel-2" role="tab" aria-selected="false">
                        Features
                    </button>
                    <button type="button" class="tab active-tab:tab-active" id="lifted-tab-3" data-tab="#lifted-panel-3" aria-controls="lifted-panel-3" role="tab" aria-selected="false">
                        Pricing
                    </button>
                </nav>
                <div class="mt-4">
                    <div id="lifted-panel-1" role="tabpanel" aria-labelledby="lifted-tab-1">
                        <p class="text-base-content/80">Get a high-level overview of everything happening in your workspace.</p>
                    </div>
                    <div id="lifted-panel-2" class="hidden" role="tabpanel" aria-labelledby="lifted-tab-2">
                        <p class="text-base-content/80">Explore all the powerful features available to boost your productivity.</p>
                    </div>
                    <div id="lifted-panel-3" class="hidden" role="tabpanel" aria-labelledby="lifted-tab-3">
                        <p class="text-base-content/80">Choose the plan that works best for your team size and needs.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pill Tabs --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Pill Tabs</h3>
                <nav class="tabs bg-base-200 rounded-lg w-fit space-x-1 p-1" aria-label="Pill Tabs" role="tablist">
                    <button type="button" class="btn btn-sm btn-text active-tab:bg-primary active-tab:text-primary-content active" id="pill-tab-1" data-tab="#pill-panel-1" aria-controls="pill-panel-1" role="tab" aria-selected="true">
                        All
                    </button>
                    <button type="button" class="btn btn-sm btn-text active-tab:bg-primary active-tab:text-primary-content" id="pill-tab-2" data-tab="#pill-panel-2" aria-controls="pill-panel-2" role="tab" aria-selected="false">
                        Active
                    </button>
                    <button type="button" class="btn btn-sm btn-text active-tab:bg-primary active-tab:text-primary-content" id="pill-tab-3" data-tab="#pill-panel-3" aria-controls="pill-panel-3" role="tab" aria-selected="false">
                        Completed
                    </button>
                    <button type="button" class="btn btn-sm btn-text active-tab:bg-primary active-tab:text-primary-content" id="pill-tab-4" data-tab="#pill-panel-4" aria-controls="pill-panel-4" role="tab" aria-selected="false">
                        Archived
                    </button>
                </nav>
                <div class="mt-4">
                    <div id="pill-panel-1" role="tabpanel" aria-labelledby="pill-tab-1">
                        <p class="text-base-content/80">Showing all items regardless of status.</p>
                    </div>
                    <div id="pill-panel-2" class="hidden" role="tabpanel" aria-labelledby="pill-tab-2">
                        <p class="text-base-content/80">Showing only active items that are in progress.</p>
                    </div>
                    <div id="pill-panel-3" class="hidden" role="tabpanel" aria-labelledby="pill-tab-3">
                        <p class="text-base-content/80">Showing completed items that have been finished.</p>
                    </div>
                    <div id="pill-panel-4" class="hidden" role="tabpanel" aria-labelledby="pill-tab-4">
                        <p class="text-base-content/80">Showing archived items that are no longer active.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Sizes --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg">Tab Sizes</h3>
                <div class="space-y-4">
                    <div>
                        <span class="text-sm text-base-content/60 mb-2 block">Extra Small (tabs-xs)</span>
                        <nav class="tabs tabs-bordered tabs-xs" role="tablist">
                            <button type="button" class="tab active-tab:tab-active active" role="tab">Tab 1</button>
                            <button type="button" class="tab active-tab:tab-active" role="tab">Tab 2</button>
                            <button type="button" class="tab active-tab:tab-active" role="tab">Tab 3</button>
                        </nav>
                    </div>
                    <div>
                        <span class="text-sm text-base-content/60 mb-2 block">Small (tabs-sm)</span>
                        <nav class="tabs tabs-bordered tabs-sm" role="tablist">
                            <button type="button" class="tab active-tab:tab-active active" role="tab">Tab 1</button>
                            <button type="button" class="tab active-tab:tab-active" role="tab">Tab 2</button>
                            <button type="button" class="tab active-tab:tab-active" role="tab">Tab 3</button>
                        </nav>
                    </div>
                    <div>
                        <span class="text-sm text-base-content/60 mb-2 block">Medium (default)</span>
                        <nav class="tabs tabs-bordered" role="tablist">
                            <button type="button" class="tab active-tab:tab-active active" role="tab">Tab 1</button>
                            <button type="button" class="tab active-tab:tab-active" role="tab">Tab 2</button>
                            <button type="button" class="tab active-tab:tab-active" role="tab">Tab 3</button>
                        </nav>
                    </div>
                    <div>
                        <span class="text-sm text-base-content/60 mb-2 block">Large (tabs-lg)</span>
                        <nav class="tabs tabs-bordered tabs-lg" role="tablist">
                            <button type="button" class="tab active-tab:tab-active active" role="tab">Tab 1</button>
                            <button type="button" class="tab active-tab:tab-active" role="tab">Tab 2</button>
                            <button type="button" class="tab active-tab:tab-active" role="tab">Tab 3</button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         DROPDOWNS SECTION
         ============================================================ --}}
    <section id="dropdowns" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">Dropdowns</h2>
        </div>

        {{-- Basic Dropdowns --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Basic Dropdowns</h3>
                <div class="flex flex-wrap gap-4">
                    {{-- Default Dropdown --}}
                    <div class="dropdown relative inline-flex">
                        <button id="dropdown-default" type="button" class="dropdown-toggle btn btn-primary" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            Dropdown
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-default">
                            <li><a class="dropdown-item" href="#">My Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><a class="dropdown-item" href="#">Billing</a></li>
                            <li><a class="dropdown-item" href="#">FAQs</a></li>
                        </ul>
                    </div>

                    {{-- Secondary Dropdown --}}
                    <div class="dropdown relative inline-flex">
                        <button id="dropdown-secondary" type="button" class="dropdown-toggle btn btn-secondary" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            Secondary
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-secondary">
                            <li><a class="dropdown-item" href="#">Option 1</a></li>
                            <li><a class="dropdown-item" href="#">Option 2</a></li>
                            <li><a class="dropdown-item" href="#">Option 3</a></li>
                        </ul>
                    </div>

                    {{-- Outline Dropdown --}}
                    <div class="dropdown relative inline-flex">
                        <button id="dropdown-outline" type="button" class="dropdown-toggle btn btn-outline btn-primary" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            Outline
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-outline">
                            <li><a class="dropdown-item" href="#">Action 1</a></li>
                            <li><a class="dropdown-item" href="#">Action 2</a></li>
                            <li><a class="dropdown-item" href="#">Action 3</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Icon Menu Dropdown --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Icon Menu Dropdown (For Cards/Actions)</h3>
                <div class="flex flex-wrap gap-4">
                    {{-- Vertical Dots --}}
                    <div class="dropdown relative inline-flex">
                        <button id="dropdown-menu-icon-v" type="button" class="dropdown-toggle btn btn-square btn-soft btn-sm" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <span class="icon-[tabler--dots-vertical] size-5"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-menu-icon-v">
                            <li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View</a></li>
                            <li><a class="dropdown-item" href="#"><span class="icon-[tabler--pencil] size-4 me-2"></span>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><span class="icon-[tabler--copy] size-4 me-2"></span>Duplicate</a></li>
                            <li><hr class="border-base-content/25 -mx-2 my-1" /></li>
                            <li><a class="dropdown-item text-error" href="#"><span class="icon-[tabler--trash] size-4 me-2"></span>Delete</a></li>
                        </ul>
                    </div>

                    {{-- Horizontal Dots --}}
                    <div class="dropdown relative inline-flex">
                        <button id="dropdown-menu-icon-h" type="button" class="dropdown-toggle btn btn-square btn-soft btn-sm" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <span class="icon-[tabler--dots] size-5"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-menu-icon-h">
                            <li><a class="dropdown-item" href="#">Share</a></li>
                            <li><a class="dropdown-item" href="#">Move</a></li>
                            <li><a class="dropdown-item" href="#">Archive</a></li>
                        </ul>
                    </div>

                    {{-- Settings Gear --}}
                    <div class="dropdown relative inline-flex">
                        <button id="dropdown-menu-gear" type="button" class="dropdown-toggle btn btn-circle btn-ghost btn-sm" aria-haspopup="menu" aria-expanded="false" aria-label="Settings">
                            <span class="icon-[tabler--settings] size-5"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-menu-gear">
                            <li><a class="dropdown-item" href="#">Preferences</a></li>
                            <li><a class="dropdown-item" href="#">Notifications</a></li>
                            <li><a class="dropdown-item" href="#">Privacy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dropdown with Icons --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Dropdown with Icons</h3>
                <div class="dropdown relative inline-flex">
                    <button id="dropdown-icons" type="button" class="dropdown-toggle btn btn-primary" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        Actions
                        <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-icons">
                        <li>
                            <a class="dropdown-item" href="#">
                                <span class="icon-[tabler--user] size-5 shrink-0"></span>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <span class="icon-[tabler--settings] size-5 shrink-0"></span>
                                Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <span class="icon-[tabler--credit-card] size-5 shrink-0"></span>
                                Billing
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <span class="icon-[tabler--help] size-5 shrink-0"></span>
                                FAQs
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Avatar Dropdown --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Avatar Dropdown (User Menu)</h3>
                <div class="dropdown relative inline-flex">
                    <button id="dropdown-avatar" type="button" class="dropdown-toggle btn btn-outline btn-primary flex items-center gap-2 rounded-full" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        <div class="avatar">
                            <div class="size-6 rounded-full">
                                <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-3.png" alt="User Avatar" />
                            </div>
                        </div>
                        John Doe
                        <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-avatar">
                        <li class="dropdown-header gap-3">
                            <div class="avatar">
                                <div class="w-10 rounded-full">
                                    <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-3.png" alt="User Avatar" />
                                </div>
                            </div>
                            <div>
                                <h6 class="text-base-content text-base font-semibold">John Doe</h6>
                                <small class="text-base-content/50 text-sm font-normal">john@example.com</small>
                            </div>
                        </li>
                        <li><hr class="border-base-content/25 -mx-2 my-1" /></li>
                        <li><a class="dropdown-item" href="#"><span class="icon-[tabler--user] size-4 me-2"></span>My Profile</a></li>
                        <li><a class="dropdown-item" href="#"><span class="icon-[tabler--settings] size-4 me-2"></span>Settings</a></li>
                        <li><a class="dropdown-item" href="#"><span class="icon-[tabler--credit-card] size-4 me-2"></span>Billing</a></li>
                        <li><hr class="border-base-content/25 -mx-2 my-1" /></li>
                        <li><a class="dropdown-item text-error" href="#"><span class="icon-[tabler--logout] size-4 me-2"></span>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Split Button Dropdown --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Split Button Dropdown</h3>
                <div class="join">
                    <button type="button" class="btn btn-primary join-item">Save</button>
                    <div class="dropdown relative inline-flex">
                        <button id="dropdown-split" type="button" class="dropdown-toggle btn btn-primary join-item" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-split">
                            <li><a class="dropdown-item" href="#">Save as Draft</a></li>
                            <li><a class="dropdown-item" href="#">Save & Continue</a></li>
                            <li><a class="dropdown-item" href="#">Save & Close</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Nested Dropdown --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg">Nested Dropdown</h3>
                <div class="dropdown relative inline-flex">
                    <button id="nested-dropdown" type="button" class="dropdown-toggle btn btn-primary" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        File
                        <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="nested-dropdown">
                        <li><a class="dropdown-item" href="#">New File</a></li>
                        <li><a class="dropdown-item" href="#">Open</a></li>
                        <li class="dropdown relative [--offset:15] [--placement:right-start] [--scope:window]">
                            <button id="nested-dropdown-export" class="dropdown-toggle dropdown-item justify-between" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                Export As
                                <span class="icon-[tabler--chevron-right] size-4 rtl:rotate-180"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48" role="menu" aria-orientation="vertical" aria-labelledby="nested-dropdown-export">
                                <li><a class="dropdown-item" href="#">PDF</a></li>
                                <li><a class="dropdown-item" href="#">Word Document</a></li>
                                <li><a class="dropdown-item" href="#">CSV</a></li>
                                <li><a class="dropdown-item" href="#">JSON</a></li>
                            </ul>
                        </li>
                        <li><hr class="border-base-content/25 -mx-2 my-1" /></li>
                        <li><a class="dropdown-item" href="#">Save</a></li>
                        <li><a class="dropdown-item" href="#">Close</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         MODALS SECTION
         ============================================================ --}}
    <section id="modals" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">Modals</h2>
        </div>

        {{-- Modal Triggers --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Modal Examples</h3>
                <div class="flex flex-wrap gap-4">
                    {{-- Basic Modal --}}
                    <button type="button" class="btn btn-primary" aria-haspopup="dialog" aria-expanded="false" aria-controls="basic-modal" data-overlay="#basic-modal">
                        Basic Modal
                    </button>

                    {{-- Confirmation Modal --}}
                    <button type="button" class="btn btn-warning" aria-haspopup="dialog" aria-expanded="false" aria-controls="confirm-modal" data-overlay="#confirm-modal">
                        Confirmation Modal
                    </button>

                    {{-- Form Modal --}}
                    <button type="button" class="btn btn-secondary" aria-haspopup="dialog" aria-expanded="false" aria-controls="form-modal" data-overlay="#form-modal">
                        Form Modal
                    </button>

                    {{-- Large Modal --}}
                    <button type="button" class="btn btn-accent" aria-haspopup="dialog" aria-expanded="false" aria-controls="large-modal" data-overlay="#large-modal">
                        Large Modal
                    </button>
                </div>
            </div>
        </div>

        {{-- Basic Modal --}}
        <div id="basic-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
            <div class="modal-dialog overlay-open:opacity-100">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Basic Modal</h3>
                        <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#basic-modal">
                            <span class="icon-[tabler--x] size-4"></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>This is a basic modal example. You can put any content here including text, images, forms, and more.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft btn-secondary" data-overlay="#basic-modal">Close</button>
                        <button type="button" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Confirmation Modal --}}
        <div id="confirm-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
            <div class="modal-dialog overlay-open:opacity-100">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center size-10 rounded-full bg-warning/20 text-warning">
                                <span class="icon-[tabler--alert-triangle] size-6"></span>
                            </span>
                            <h3 class="modal-title">Delete Confirmation</h3>
                        </div>
                        <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#confirm-modal">
                            <span class="icon-[tabler--x] size-4"></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft btn-secondary" data-overlay="#confirm-modal">Cancel</button>
                        <button type="button" class="btn btn-error">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Modal --}}
        <div id="form-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
            <div class="modal-dialog overlay-open:opacity-100">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Create New Task</h3>
                        <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#form-modal">
                            <span class="icon-[tabler--x] size-4"></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="space-y-4">
                            <div>
                                <label class="label label-text" for="task-title">Task Title</label>
                                <input type="text" class="input w-full" id="task-title" placeholder="Enter task title" />
                            </div>
                            <div>
                                <label class="label label-text" for="task-desc">Description</label>
                                <textarea class="textarea w-full" id="task-desc" rows="3" placeholder="Enter task description"></textarea>
                            </div>
                            <div>
                                <label class="label label-text" for="task-priority">Priority</label>
                                <select class="select w-full" id="task-priority">
                                    <option>Low</option>
                                    <option>Medium</option>
                                    <option>High</option>
                                    <option>Urgent</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft btn-secondary" data-overlay="#form-modal">Cancel</button>
                        <button type="button" class="btn btn-primary">Create Task</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Large Modal --}}
        <div id="large-modal" class="overlay modal overlay-open:opacity-100 hidden" role="dialog" tabindex="-1">
            <div class="modal-dialog modal-dialog-lg overlay-open:opacity-100">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Large Modal</h3>
                        <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#large-modal">
                            <span class="icon-[tabler--x] size-4"></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-4">This is a large modal that can contain more content. It's useful for displaying detailed information, complex forms, or previews.</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-base-200 p-4 rounded-lg">
                                <h4 class="font-semibold mb-2">Section 1</h4>
                                <p class="text-sm text-base-content/70">Content for section one goes here.</p>
                            </div>
                            <div class="bg-base-200 p-4 rounded-lg">
                                <h4 class="font-semibold mb-2">Section 2</h4>
                                <p class="text-sm text-base-content/70">Content for section two goes here.</p>
                            </div>
                            <div class="bg-base-200 p-4 rounded-lg">
                                <h4 class="font-semibold mb-2">Section 3</h4>
                                <p class="text-sm text-base-content/70">Content for section three goes here.</p>
                            </div>
                            <div class="bg-base-200 p-4 rounded-lg">
                                <h4 class="font-semibold mb-2">Section 4</h4>
                                <p class="text-sm text-base-content/70">Content for section four goes here.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft btn-secondary" data-overlay="#large-modal">Close</button>
                        <button type="button" class="btn btn-primary">Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         ADVANCED SELECT SECTION
         ============================================================ --}}
    <section id="advanced-select" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">Advanced Select</h2>
        </div>

        {{-- Basic Advanced Select --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Basic Advanced Select (Direct Match Off)</h3>
                <p class="text-sm text-base-content/60 mb-4">Search allows partial matching anywhere in the option text.</p>
                <div class="max-w-sm">
                    <select data-select='{
                        "placeholder": "Select an option...",
                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                        "toggleClasses": "advance-select-toggle",
                        "dropdownClasses": "advance-select-menu",
                        "optionClasses": "advance-select-option selected:select-active",
                        "isSearchDirectMatch": false,
                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                    }' class="hidden">
                        <option value="">Choose</option>
                        <option value="design">Design</option>
                        <option value="development">Development</option>
                        <option value="marketing">Marketing</option>
                        <option value="sales">Sales</option>
                        <option value="support">Customer Support</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Multiple Selection with Avatars --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Multiple Selection with User Avatars</h3>
                <p class="text-sm text-base-content/60 mb-4">Select multiple team members with avatar preview.</p>
                <div class="max-w-md">
                    <label class="label label-text" for="team-members">Assign Team Members</label>
                    <select id="team-members" multiple data-select='{
                        "placeholder": "Select team members...",
                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                        "toggleClasses": "advance-select-toggle",
                        "dropdownClasses": "advance-select-menu",
                        "optionClasses": "advance-select-option selected:select-active",
                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><div class=\"flex items-center gap-3\"><div data-icon></div><div class=\"text-sm text-base-content\" data-title></div></div><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                    }' class="hidden">
                        <option value="">Choose</option>
                        <option value="1" data-select-option='{
                            "icon": "<img class=\"shrink-0 size-6 rounded-full\" src=\"https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png\" alt=\"Emma Wilson\" />"
                        }'>Emma Wilson</option>
                        <option value="2" data-select-option='{
                            "icon": "<img class=\"shrink-0 size-6 rounded-full\" src=\"https://cdn.flyonui.com/fy-assets/avatar/avatar-2.png\" alt=\"James Chen\" />"
                        }'>James Chen</option>
                        <option value="3" data-select-option='{
                            "icon": "<img class=\"shrink-0 size-6 rounded-full\" src=\"https://cdn.flyonui.com/fy-assets/avatar/avatar-3.png\" alt=\"Sofia Rodriguez\" />"
                        }'>Sofia Rodriguez</option>
                        <option value="4" data-select-option='{
                            "icon": "<img class=\"shrink-0 size-6 rounded-full\" src=\"https://cdn.flyonui.com/fy-assets/avatar/avatar-4.png\" alt=\"Michael Brown\" />"
                        }'>Michael Brown</option>
                        <option value="5" data-select-option='{
                            "icon": "<img class=\"shrink-0 size-6 rounded-full\" src=\"https://cdn.flyonui.com/fy-assets/avatar/avatar-5.png\" alt=\"Sarah Johnson\" />"
                        }'>Sarah Johnson</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tags Mode --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg">Tags Mode</h3>
                <p class="text-sm text-base-content/60 mb-4">Type to create custom tags or select from existing options.</p>
                <div class="max-w-md">
                    <label class="label label-text" for="project-tags">Project Tags</label>
                    <select id="project-tags" multiple data-select='{
                        "placeholder": "Add tags...",
                        "mode": "tags",
                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                        "toggleClasses": "advance-select-toggle",
                        "dropdownClasses": "advance-select-menu",
                        "optionClasses": "advance-select-option selected:select-active",
                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                    }' class="hidden">
                        <option value="urgent">Urgent</option>
                        <option value="bug">Bug</option>
                        <option value="feature">Feature</option>
                        <option value="enhancement">Enhancement</option>
                        <option value="documentation">Documentation</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         PASSWORD COMPONENTS SECTION
         ============================================================ --}}
    <section id="password" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">Password Components</h2>
        </div>

        {{-- Toggle Password --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Toggle Password Visibility</h3>
                <div class="max-w-sm">
                    <label class="label label-text" for="toggle-password-demo">Password</label>
                    <div class="input flex items-center gap-2">
                        <span class="icon-[tabler--lock] text-base-content/60 size-5"></span>
                        <input id="toggle-password-demo" type="password" class="grow bg-transparent border-0 focus:outline-none" placeholder="Enter password" value="MySecurePassword123!" />
                        <button type="button" data-toggle-password='{ "target": "#toggle-password-demo" }' class="block cursor-pointer" aria-label="Toggle password visibility">
                            <span class="icon-[tabler--eye] text-base-content/60 password-active:block hidden size-5 shrink-0"></span>
                            <span class="icon-[tabler--eye-off] text-base-content/60 password-active:hidden block size-5 shrink-0"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Password Strength with Indicator --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Password Strength Indicator</h3>
                <div class="max-w-sm">
                    <label class="label label-text" for="password-strength-demo">Create Password</label>
                    <div class="input flex items-center gap-2 mb-2">
                        <span class="icon-[tabler--lock] text-base-content/60 size-5"></span>
                        <input type="password" id="password-strength-demo" class="grow bg-transparent border-0 focus:outline-none" placeholder="Enter a strong password" />
                        <button type="button" data-toggle-password='{ "target": "#password-strength-demo" }' class="block cursor-pointer" aria-label="Toggle password visibility">
                            <span class="icon-[tabler--eye] text-base-content/60 password-active:block hidden size-5 shrink-0"></span>
                            <span class="icon-[tabler--eye-off] text-base-content/60 password-active:hidden block size-5 shrink-0"></span>
                        </button>
                    </div>
                    {{-- Strength Indicator Bar --}}
                    <div data-strong-password='{
                        "target": "#password-strength-demo",
                        "hints": "#password-strength-hints",
                        "stripClasses": "strong-password:bg-primary strong-password-accepted:bg-success h-1.5 flex-auto rounded-full bg-base-content/20"
                    }' class="flex gap-1 mt-2">
                    </div>
                </div>
            </div>
        </div>

        {{-- Password Strength with Hints --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg">Password Strength with Requirements</h3>
                <div class="max-w-md">
                    <label class="label label-text" for="password-hints-demo">Create Password</label>
                    <div class="input flex items-center gap-2 mb-2">
                        <span class="icon-[tabler--lock] text-base-content/60 size-5"></span>
                        <input type="password" id="password-hints-demo" class="grow bg-transparent border-0 focus:outline-none" placeholder="Enter a strong password" />
                        <button type="button" data-toggle-password='{ "target": "#password-hints-demo" }' class="block cursor-pointer" aria-label="Toggle password visibility">
                            <span class="icon-[tabler--eye] text-base-content/60 password-active:block hidden size-5 shrink-0"></span>
                            <span class="icon-[tabler--eye-off] text-base-content/60 password-active:hidden block size-5 shrink-0"></span>
                        </button>
                    </div>
                    {{-- Strength Indicator Bar --}}
                    <div data-strong-password='{
                        "target": "#password-hints-demo",
                        "hints": "#password-hints-content",
                        "stripClasses": "strong-password:bg-primary strong-password-accepted:bg-success h-1.5 flex-auto rounded-full bg-base-content/20"
                    }' class="flex gap-1 mt-2">
                    </div>
                    {{-- Password Hints --}}
                    <div id="password-hints-content" class="mt-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm text-base-content">Strength:</span>
                            <span data-pw-strength-hint='["Empty", "Weak", "Medium", "Strong", "Very Strong", "Super Strong"]' class="text-sm font-semibold text-base-content"></span>
                        </div>
                        <h6 class="text-sm font-semibold text-base-content mb-2">Your password must contain:</h6>
                        <ul class="space-y-1 text-sm">
                            <li data-pw-strength-rule="min-length" class="strong-password-active:text-success flex items-center gap-2 text-base-content/60">
                                <span class="icon-[tabler--circle-check] hidden size-4 shrink-0" data-check></span>
                                <span class="icon-[tabler--circle-x] hidden size-4 shrink-0" data-uncheck></span>
                                Minimum 6 characters
                            </li>
                            <li data-pw-strength-rule="lowercase" class="strong-password-active:text-success flex items-center gap-2 text-base-content/60">
                                <span class="icon-[tabler--circle-check] hidden size-4 shrink-0" data-check></span>
                                <span class="icon-[tabler--circle-x] hidden size-4 shrink-0" data-uncheck></span>
                                At least one lowercase letter
                            </li>
                            <li data-pw-strength-rule="uppercase" class="strong-password-active:text-success flex items-center gap-2 text-base-content/60">
                                <span class="icon-[tabler--circle-check] hidden size-4 shrink-0" data-check></span>
                                <span class="icon-[tabler--circle-x] hidden size-4 shrink-0" data-uncheck></span>
                                At least one uppercase letter
                            </li>
                            <li data-pw-strength-rule="numbers" class="strong-password-active:text-success flex items-center gap-2 text-base-content/60">
                                <span class="icon-[tabler--circle-check] hidden size-4 shrink-0" data-check></span>
                                <span class="icon-[tabler--circle-x] hidden size-4 shrink-0" data-uncheck></span>
                                At least one number
                            </li>
                            <li data-pw-strength-rule="special-characters" class="strong-password-active:text-success flex items-center gap-2 text-base-content/60">
                                <span class="icon-[tabler--circle-check] hidden size-4 shrink-0" data-check></span>
                                <span class="icon-[tabler--circle-x] hidden size-4 shrink-0" data-uncheck></span>
                                At least one special character (!@#$%^&*)
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         DATETIME PICKER SECTION
         ============================================================ --}}
    <section id="datetime" class="mb-16">
        <div class="divider divider-start">
            <h2 class="text-2xl font-bold">DateTime Picker (Flatpickr)</h2>
        </div>

        {{-- Basic Date Picker --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Basic Date Picker</h3>
                <div class="max-w-sm">
                    <label class="label label-text" for="flatpickr-date">Select Date</label>
                    <div class="input flex items-center gap-2">
                        <span class="icon-[tabler--calendar] text-base-content/60 size-5"></span>
                        <input type="text" class="grow bg-transparent border-0 focus:outline-none" placeholder="YYYY-MM-DD" id="flatpickr-date" />
                    </div>
                </div>
            </div>
        </div>

        {{-- DateTime Picker --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">DateTime Picker (Date + Time)</h3>
                <div class="max-w-sm">
                    <label class="label label-text" for="flatpickr-datetime">Select Date & Time</label>
                    <div class="input flex items-center gap-2">
                        <span class="icon-[tabler--calendar-time] text-base-content/60 size-5"></span>
                        <input type="text" class="grow bg-transparent border-0 focus:outline-none" placeholder="YYYY-MM-DD HH:MM" id="flatpickr-datetime" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Date Range Picker --}}
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="card-title text-lg">Date Range Picker</h3>
                <div class="max-w-md">
                    <label class="label label-text" for="flatpickr-range">Select Date Range</label>
                    <div class="input flex items-center gap-2">
                        <span class="icon-[tabler--calendar-event] text-base-content/60 size-5"></span>
                        <input type="text" class="grow bg-transparent border-0 focus:outline-none" placeholder="Start date to End date" id="flatpickr-range" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Month/Year Dropdown --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg">With Month/Year Dropdowns</h3>
                <p class="text-sm text-base-content/60 mb-4">Quick navigation using dropdown selectors for month and year.</p>
                <div class="max-w-sm">
                    <label class="label label-text" for="flatpickr-dropdown">Select Date</label>
                    <div class="input flex items-center gap-2">
                        <span class="icon-[tabler--calendar] text-base-content/60 size-5"></span>
                        <input type="text" class="grow bg-transparent border-0 focus:outline-none" placeholder="YYYY-MM-DD" id="flatpickr-dropdown" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Back to Top --}}
    <div class="fixed bottom-8 right-8">
        <a href="#" class="btn btn-circle btn-primary shadow-lg">
            <span class="icon-[tabler--arrow-up] size-5"></span>
        </a>
    </div>
</div>

{{-- Flatpickr Initialization Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Basic Date Picker
    if (document.getElementById('flatpickr-date') && typeof flatpickr !== 'undefined') {
        flatpickr('#flatpickr-date', {
            dateFormat: 'Y-m-d',
            monthSelectorType: 'static'
        });
    }

    // DateTime Picker
    if (document.getElementById('flatpickr-datetime') && typeof flatpickr !== 'undefined') {
        flatpickr('#flatpickr-datetime', {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true,
            monthSelectorType: 'static'
        });
    }

    // Date Range Picker
    if (document.getElementById('flatpickr-range') && typeof flatpickr !== 'undefined') {
        flatpickr('#flatpickr-range', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            monthSelectorType: 'static'
        });
    }

    // Month/Year Dropdown Picker
    if (document.getElementById('flatpickr-dropdown') && typeof flatpickr !== 'undefined') {
        flatpickr('#flatpickr-dropdown', {
            dateFormat: 'Y-m-d',
            monthSelectorType: 'dropdown'
        });
    }
});
</script>
@endsection
