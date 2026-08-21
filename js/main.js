document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Navigation Toggle
    const navToggle = document.getElementById('mobileNavToggle');
    const navLinks = document.getElementById('navLinks');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('show');
            const isExpanded = navLinks.classList.contains('show');
            navToggle.setAttribute('aria-expanded', isExpanded);
        });
    }

    // 2. Delete Confirmation Handler
    const deleteButtons = document.querySelectorAll('.js-confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const confirmed = confirm('Are you sure you want to delete this travel story? This action cannot be undone.');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });

    // 3. Blog Editor - Live Image Preview
    const imageUrlInput = document.getElementById('imageUrlInput');
    const imagePreviewWrapper = document.getElementById('imagePreviewWrapper');
    const imagePreview = document.getElementById('imagePreview');

    if (imageUrlInput && imagePreviewWrapper && imagePreview) {
        const updatePreview = () => {
            const url = imageUrlInput.value.trim();
            if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
                imagePreview.src = url;
                imagePreviewWrapper.style.display = 'block';
                imagePreview.onerror = () => {
                    imagePreviewWrapper.style.display = 'none';
                };
            } else {
                imagePreviewWrapper.style.display = 'none';
            }
        };

        imageUrlInput.addEventListener('input', updatePreview);
        updatePreview();
    }

    // 4. Registration Password Match Validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordError = document.getElementById('passwordMatchError');

        registerForm.addEventListener('submit', (e) => {
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                if (passwordError) {
                    passwordError.textContent = 'Passwords do not match. Please re-enter.';
                    passwordError.style.display = 'block';
                } else {
                    alert('Passwords do not match. Please make sure both passwords match.');
                }
                confirmPassword.focus();
            }
        });

        if (confirmPassword && passwordError) {
            confirmPassword.addEventListener('input', () => {
                if (password.value !== confirmPassword.value) {
                    passwordError.textContent = 'Passwords do not match.';
                    passwordError.style.display = 'block';
                } else {
                    passwordError.style.display = 'none';
                }
            });
        }
    }

    // 5. Flash Alert Dismissal
    const alertCloseButtons = document.querySelectorAll('.alert-close');
    alertCloseButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const alertBox = e.target.closest('.alert');
            if (alertBox) {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 250);
            }
        });
    });

    // Auto-dismiss success and info alerts
    const autoDismissAlerts = document.querySelectorAll('.alert-success, .alert-info');
    autoDismissAlerts.forEach(alertBox => {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alertBox.style.opacity = '0';
            alertBox.style.transform = 'translateY(-10px)';
            setTimeout(() => alertBox.remove(), 500);
        }, 5000);
    });
});