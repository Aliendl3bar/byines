document.addEventListener('click', function(e) {
    var toggle = e.target.closest('[data-action="toggle-password"]');
    if (toggle) {
        var fieldId = toggle.getAttribute('data-field');
        var field = document.getElementById(fieldId);
        if (field) {
            field.type = field.type === 'password' ? 'text' : 'password';
        }
    }
});
