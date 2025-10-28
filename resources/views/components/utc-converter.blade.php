{{--
    UTC to Local Datetime Converter Component
    Converts UTC datetime values from data-utc attributes to local datetime-local inputs
--}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        /**
         * Convert Date object to datetime-local input format
         * @param {Date} date
         * @returns {string} Format: yyyy-MM-ddTHH:mm
         */
        function toLocalDateTimeInputValue(date) {
            const yr = date.getFullYear();
            const mo = String(date.getMonth() + 1).padStart(2, '0');
            const da = String(date.getDate()).padStart(2, '0');
            const hr = String(date.getHours()).padStart(2, '0');
            const mi = String(date.getMinutes()).padStart(2, '0');
            return `${yr}-${mo}-${da}T${hr}:${mi}`;
        }

        /**
         * Format Date to user's local timezone string
         * @param {Date} date
         * @returns {string}
         */
        function formatLocalDateTime(date) {
            try {
                return date.toLocaleString(undefined, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } catch (e) {
                return date.toString();
            }
        }

        // Convert datetime-local inputs with data-utc attribute
        document.querySelectorAll('input[type="datetime-local"][data-utc]').forEach(input => {
            // Skip if user has validation errors (old input exists)
            if (input.value && input.value.length) return;
            
            const utc = input.getAttribute('data-utc');
            if (!utc) return;
            
            try {
                const d = new Date(utc);
                if (!isNaN(d.getTime())) {
                    input.value = toLocalDateTimeInputValue(d);
                }
            } catch (e) {
                console.error('Failed to convert UTC datetime:', e);
            }
        });

        // Convert display elements with data-utc attribute
        document.querySelectorAll('[data-utc]:not(input)').forEach(element => {
            const utc = element.getAttribute('data-utc');
            if (!utc) return;
            
            try {
                const d = new Date(utc);
                if (!isNaN(d.getTime())) {
                    element.textContent = formatLocalDateTime(d);
                    element.classList.remove('no-js');
                }
            } catch (e) {
                console.error('Failed to format UTC datetime:', e);
            }
        });
    });
</script>
