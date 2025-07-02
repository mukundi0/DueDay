document.addEventListener('DOMContentLoaded', () => {
    // --- For confirmation dialogs ---
    const confirmationForms = document.querySelectorAll('form[data-confirm]');
    confirmationForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const message = form.dataset.confirm;
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // --- For "Select All" checkbox functionality ---
    const selectAllCheckbox = document.getElementById('selectAllUsers');
    // Find all checkboxes that have the class 'user-checkbox'
    const userCheckboxes = document.querySelectorAll('.user-checkbox');

    if (selectAllCheckbox && userCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            // When the 'selectAllUsers' checkbox changes, loop through all user checkboxes
            // and set their 'checked' state to match.
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
});