{{-- Applies the theme the member last picked before the page is drawn, so dark mode does not flash white. --}}
<script>
    try {
        if (localStorage.getItem('knowvia-theme') === 'dark') {
            document.documentElement.dataset.theme = 'dark';
        }
    } catch (error) {
        // Storage can be blocked (private windows); the light theme is used then.
    }
</script>
