// Adds a small "×" button inside every input marked with data-clearable.
// The button only shows while the field has text, and empties the field when clicked.
document.querySelectorAll('input[data-clearable]').forEach(function(input) {

    let wrapper = input.closest('.password-wrapper');

    if (!wrapper) {
        wrapper = document.createElement('div');
        wrapper.className = 'clearable-wrapper';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
    }

    wrapper.classList.add('has-clear-button');

    const label = document.querySelector('label[for="' + input.id + '"]');
    const fieldName = label
        ? label.childNodes[0].textContent.trim()
        : 'field';

    const clearButton = document.createElement('button');
    clearButton.type = 'button';
    clearButton.className = 'clear-field';
    clearButton.textContent = '×';
    clearButton.setAttribute('aria-label', 'Clear ' + fieldName);

    input.insertAdjacentElement('afterend', clearButton);

    function updateClearButton() {
        clearButton.hidden = input.value === '';
    }

    clearButton.addEventListener('click', function() {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
    });

    input.addEventListener('input', updateClearButton);
    input.addEventListener('change', updateClearButton);

    updateClearButton();
});
