import './bootstrap';

import Alpine from 'alpinejs';

// Attach Alpine to the global object using globalThis (preferred over window)
globalThis.Alpine = Alpine;

Alpine.start();

// Shared negative marking logic
import './negative-marks';
