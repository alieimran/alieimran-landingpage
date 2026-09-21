{{-- Runs synchronously, before first paint, to avoid a flash of the wrong
     theme. Must stay a plain inline <script> (not @vite'd/deferred) so it
     executes before the page renders. When CSP headers are added later,
     this will need a nonce or hash added to the script-src allowlist.

     Exposes window.__setTheme(isDark) so <x-theme-toggle> can flip the
     theme and keep the theme-color meta tag (mobile browser chrome) in
     sync, without duplicating this logic in the toggle component. --}}
<script>
    (function () {
        function applyTheme(isDark) {
            document.documentElement.classList.toggle('dark', isDark);

            var meta = document.querySelector('meta[name="theme-color"]');
            if (meta) {
                meta.setAttribute('content', isDark ? '#05080a' : '#ffffff');
            }
        }

        var stored = localStorage.getItem('theme');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        applyTheme(stored === 'dark' || (!stored && prefersDark));

        window.__setTheme = function (isDark) {
            applyTheme(isDark);
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        };
    })();
</script>
