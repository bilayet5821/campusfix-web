document.querySelectorAll('[data-image-input]').forEach((input) => {
    const preview = document.getElementById(input.dataset.imageInput);
    const feedback = document.getElementById(input.dataset.fileFeedback);
    const maxBytes = Number(input.dataset.maxBytes) || 5 * 1024 * 1024;
    const acceptedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    let selectedFiles = [];

    const syncInput = () => {
        const transfer = new DataTransfer();
        selectedFiles.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    };

    const render = () => {
        if (!preview) return;
        preview.replaceChildren();

        selectedFiles.forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'attachment-preview';

            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = `Preview of ${file.name}`;
            image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'attachment-remove';
            remove.setAttribute('aria-label', `Remove ${file.name}`);
            remove.textContent = '×';
            remove.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                syncInput();
                render();
            });

            const name = document.createElement('span');
            name.textContent = file.name;
            card.append(image, remove, name);
            preview.append(card);
        });
    };

    input.addEventListener('change', () => {
        const incoming = Array.from(input.files ?? []);
        const invalid = incoming.filter((file) => !acceptedTypes.includes(file.type) || file.size > maxBytes);
        const valid = incoming.filter((file) => acceptedTypes.includes(file.type) && file.size <= maxBytes);

        selectedFiles = [...selectedFiles, ...valid].slice(0, 5);
        syncInput();
        render();

        if (feedback) {
            feedback.textContent = invalid.length
                ? `${invalid.length} file${invalid.length > 1 ? 's were' : ' was'} skipped. Use JPG, PNG, or WebP under 5 MB.`
                : selectedFiles.length ? `${selectedFiles.length} of 5 images selected.` : '';
            feedback.classList.toggle('text-danger', invalid.length > 0);
        }
    });
});
