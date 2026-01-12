<footer class="footer footer-center p-6 bg-base-100 text-base-content border-t border-base-200">
    <div class="flex flex-col md:flex-row items-center justify-between w-full max-w gap-4">
        <div class="flex items-center gap-2">
            <span class="icon-[tabler--checkbox] size-5 text-primary"></span>
            <span class="font-semibold">{{ config('app.name', 'NewDone') }}</span>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-base-content/60">
            <a href="/help" class="hover:text-primary transition-colors">Help Center</a>
            <span class="hidden sm:inline">|</span>
            <a href="/privacy" class="hover:text-primary transition-colors">Privacy Policy</a>
            <span class="hidden sm:inline">|</span>
            <a href="/terms" class="hover:text-primary transition-colors">Terms of Service</a>
        </div>
        <p class="text-sm text-base-content/50">
            &copy; {{ date('Y') }} {{ config('app.name', 'NewDone') }}. All rights reserved.
        </p>
    </div>
</footer>
