document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.setAttribute('aria-pressed', String(!showing));
        button.querySelector('[data-toggle-label]').textContent = showing ? 'Show' : 'Hide';
    });
});

document.querySelectorAll('[data-character-count]').forEach((field) => {
    const output = document.getElementById(field.dataset.characterCount);
    const limit = Number(field.getAttribute('maxlength')) || 0;

    const updateCount = () => {
        if (output) output.textContent = `${field.value.length}${limit ? ` / ${limit}` : ''}`;
    };

    updateCount();
    field.addEventListener('input', updateCount);
});

document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.querySelector(':invalid')?.focus();
        }

        form.classList.add('was-validated');
    });
});
