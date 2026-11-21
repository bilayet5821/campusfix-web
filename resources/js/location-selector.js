document.querySelectorAll('[data-building-select]').forEach((buildingSelect) => {
    const roomSelectId = buildingSelect.dataset.buildingSelect;
    const roomSelect = document.getElementById(roomSelectId);

    if (!roomSelect) return;

    const allRoomOptions = Array.from(roomSelect.querySelectorAll('option[data-building-id]')).map((option) => ({
        value: option.value,
        label: option.textContent.trim(),
        buildingId: option.dataset.buildingId,
    }));

    const initialSelectedRoom = roomSelect.dataset.selectedLocation || roomSelect.value || '';

    const populateRooms = (preserveValue = '') => {
        const selectedBuildingId = buildingSelect.value;
        roomSelect.innerHTML = '';

        if (!selectedBuildingId) {
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select a building first';
            roomSelect.appendChild(placeholder);
            roomSelect.disabled = true;
            return;
        }

        const matchingRooms = allRoomOptions.filter((room) => room.buildingId === selectedBuildingId);

        if (matchingRooms.length === 0) {
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = 'No rooms available in this building';
            roomSelect.appendChild(emptyOption);
            roomSelect.disabled = true;
            return;
        }

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Choose a room or area';
        roomSelect.appendChild(placeholder);

        matchingRooms.forEach((room) => {
            const option = document.createElement('option');
            option.value = room.value;
            option.textContent = room.label;
            option.dataset.buildingId = room.buildingId;
            if (preserveValue && preserveValue === room.value) {
                option.selected = true;
            }
            roomSelect.appendChild(option);
        });

        roomSelect.disabled = false;
    };

    populateRooms(initialSelectedRoom);

    buildingSelect.addEventListener('change', () => {
        populateRooms('');
    });
});
