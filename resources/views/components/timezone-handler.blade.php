{{-- Timezone Handler Component --}}
<input type="hidden" name="timezone" id="timezone">
<input type="hidden" id="server-tz" value="{{ config('app.timezone') }}">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Populate browser timezone
        try {
            let tz = (Intl && Intl.DateTimeFormat && Intl.DateTimeFormat().resolvedOptions) 
                ? Intl.DateTimeFormat().resolvedOptions().timeZone 
                : null;
            
            if (!tz) {
                const st = document.getElementById('server-tz');
                tz = st ? st.value : 'UTC';
            }
            
            const tzInput = document.getElementById('timezone');
            if (tzInput) tzInput.value = tz;
        } catch (e) {
            console.error('Timezone detection failed:', e);
        }
    });
</script>
