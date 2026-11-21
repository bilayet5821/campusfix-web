document.querySelectorAll('[data-role-defaults]').forEach((roleSelect) => {
    const form = roleSelect.closest('form');
    if (!form) return;

    let defaultsMap = {};
    try {
        defaultsMap = JSON.parse(roleSelect.dataset.roleDefaults || '{}');
    } catch {
        defaultsMap = {};
    }

    const permissionInputs = Array.from(form.querySelectorAll('input[name="permissions[]"]'));
    const studentIdInput = form.querySelector('#student_id');
    const studentIdRequiredMark = form.querySelector('[data-student-id-required]');
    const applyButton = form.querySelector('[data-apply-role-defaults]');
    const autoApplyOnRoleChange = roleSelect.dataset.autoApplyDefaults === 'true';

    const syncStudentIdRequirement = () => {
        const isStudent = roleSelect.value === 'student';
        if (studentIdInput) {
            studentIdInput.required = isStudent;
        }
        if (studentIdRequiredMark) {
            studentIdRequiredMark.classList.toggle('d-none', !isStudent);
        }
    };

    const applyRoleDefaults = () => {
        const defaults = defaultsMap[roleSelect.value] || [];
        permissionInputs.forEach((checkbox) => {
            checkbox.checked = defaults.includes(checkbox.value);
        });
    };

    syncStudentIdRequirement();

    roleSelect.addEventListener('change', () => {
        syncStudentIdRequirement();
        if (autoApplyOnRoleChange) {
            applyRoleDefaults();
        }
    });

    applyButton?.addEventListener('click', () => {
        applyRoleDefaults();
    });
});
