// Shared negative marks utilities
// Exposes functions on window so inline onchange handlers can call them.

function formatNumber(value) {
    // Format to 2 decimal places when appropriate but keep common fractions readable
    if (Number.isInteger(Number(value))) return String(value);
    return Number(value).toFixed(2).replace(/\.00$/, '');
}

function renderNegativeOptions(marks) {
    // marks is a Number (can be decimal). We return array of {value, label}
    // The values represent absolute negative marks relative to question marks.
    const m = Number(marks) || 1;
    const options = [
        { value: 0, label: 'No negative marking' },
        { value: +(m * 0.25).toFixed(6), label: `1/4 (${formatNumber((m * 0.25).toFixed(6))})` },
        { value: +(m * (1/3)).toFixed(6), label: `1/3 (${formatNumber((m * (1/3)).toFixed(6))})` },
        { value: +(m * 0.5).toFixed(6), label: `1/2 (${formatNumber((m * 0.5).toFixed(6))})` },
        { value: +(m * 1).toFixed(6), label: `Full (${formatNumber((m * 1).toFixed(6))})` },
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
        opt.value = o.value;
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
        const negSelect = document.getElementById(`negative_marks_${id}`) || document.querySelector(`#negative_marks\[${id}\]`);
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
