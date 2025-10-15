// Shared negative marks utilities
// Exposes functions on window so inline onchange handlers can call them.

function formatNumber(value) {
    // Convert to number and format:
    // - If it's an integer (after numeric conversion) show without decimals (e.g. 4)
    // - Otherwise show two decimals rounded (e.g. 1.3333 -> 1.33, 1.6666 -> 1.67)
    const n = Number(value);
    if (Number.isNaN(n)) return String(value);
    if (Number.isInteger(n)) return String(n);
    return n.toFixed(2);
}

function renderNegativeOptions(marks) {
    // marks is a Number (can be decimal). We return array of {value, label}
    // The values represent absolute negative marks relative to question marks.
    const m = Number(marks) || 1;
    const options = [
        { value: 0, label: 'No negative marking' },
        { value: +(m * 0.25).toFixed(2), label: `1/4 (${formatNumber((m * 0.25).toFixed(2))})` },
        { value: +(m * (1/3)).toFixed(2), label: `1/3 (${formatNumber((m * (1/3)).toFixed(2))})` },
        { value: +(m * 0.5).toFixed(2), label: `1/2 (${formatNumber((m * 0.5).toFixed(2))})` },
        { value: +(m * 1).toFixed(2), label: `Full (${formatNumber((m * 1).toFixed(2))})` },
    ];
    return options;
}

function updateNegativeOptionsForSelect(selectEl, marks, selectedValue) {
    if (!selectEl) return;
    const opts = renderNegativeOptions(marks);
    // clear
    selectEl.innerHTML = '';
    opts.forEach(o => {
        const opt = document.createElement('option');
        // Ensure value is a string so select.value comparisons work predictably
        opt.value = String(o.value);
        opt.textContent = o.label;
        selectEl.appendChild(opt);
    });
    if (typeof selectedValue !== 'undefined') {
        selectEl.value = String(selectedValue);
    }
}

function toggleNegativeSelectVisibility(toggleEl, selectEl) {
    const v = (toggleEl && (toggleEl.value || toggleEl.getAttribute('value'))) || toggleEl;
    const isYes = String(v) === 'yes' || String(v) === 'true' || v === true;
    if (selectEl) {
        if (isYes) selectEl.classList.remove('hidden');
        else selectEl.classList.add('hidden');
    }
}

function initNegativeMarksForPage() {
    // Find any selects with data-question-id or the default_negative_marks
    // Default marks control
    const defaultMarks = document.getElementById('default_marks');
    if (defaultMarks) {
        const dd = document.getElementById('default_negative_marks');
        updateNegativeOptionsForSelect(dd, defaultMarks.value, dd ? dd.getAttribute('data-selected') : undefined);
        const toggle = document.getElementById('default_negative_marks_enabled');
        if (toggle) toggleNegativeSelectVisibility(toggle, dd);
        defaultMarks.addEventListener('change', () => {
            updateNegativeOptionsForSelect(dd, defaultMarks.value);
        });
    }

    // Per-question elements
    document.querySelectorAll('.question-marks').forEach(input => {
        const id = input.dataset.questionId;
        const marksVal = input.value || input.getAttribute('value') || 1;
    // Try the id first, otherwise fallback to attribute selector (handles odd IDs)
    const negSelect = document.getElementById(`negative_marks_${id}`) || document.querySelector(`[data-question-id="${id}"]#negative_marks_${id}`) || document.querySelector(`#negative_marks_${id}`);
        if (negSelect) updateNegativeOptionsForSelect(negSelect, marksVal, negSelect.getAttribute('data-selected'));
        const toggle = document.getElementById(`negative_enabled_${id}`) || document.querySelector(`#negative_enabled_${id}`);
        if (toggle && negSelect) toggleNegativeSelectVisibility(toggle, negSelect);
        // When marks change for a question, update its negative options
        input.addEventListener('change', (e) => {
            const newMarks = e.target.value;
            updateNegativeOptionsForSelect(negSelect, newMarks);
        });
    });
}

// Expose globally
window.NegativeMarks = {
    renderNegativeOptions,
    updateNegativeOptionsForSelect,
    toggleNegativeSelectVisibility,
    initNegativeMarksForPage,
};

export default window.NegativeMarks;
