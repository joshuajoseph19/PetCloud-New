/**
 * PetCloud Form Validation Library
 * Handles real-time validation for all forms
 */

document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            // Real-time validation on blur
            input.addEventListener('blur', function () {
                validateField(input);
            });

            // Specific Phone Validation
            if (input.name === 'phone' || input.type === 'tel') {
                input.addEventListener('input', function (e) {
                    // Only allow numbers, +, - and spaces
                    this.value = this.value.replace(/[^0-9+\- ]/g, '');
                });
            }
        });

        form.addEventListener('submit', function (e) {
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.error-message');
                if (firstError) {
                    firstError.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });

    function validateField(input) {
        const value = input.value.trim();
        const parent = input.parentElement;
        let errorMsg = '';

        // Remove existing error
        const existingError = parent.querySelector('.error-message');
        if (existingError) existingError.remove();
        input.style.borderColor = '#e5e7eb';

        // Required Check
        if (input.hasAttribute('required') && value === '') {
            errorMsg = 'This field is required.';
        }

        // Email Check
        else if (input.type === 'email' && value !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                errorMsg = 'Please enter a valid email address.';
            }
        }

        // Phone Check
        else if ((input.name === 'phone' || input.type === 'tel') && value !== '') {
            // Basic universal phone regex: 10 to 15 digits, allows +, -, spaces
            const phoneRegex = /^[+]?[0-9\-\s]{10,15}$/;
            if (!phoneRegex.test(value)) {
                errorMsg = 'Please enter a valid phone number (min 10 digits).';
            }
        }

        // Min Length Check
        else if (input.hasAttribute('minlength')) {
            const min = input.getAttribute('minlength');
            if (value.length < min) {
                errorMsg = `Must be at least ${min} characters long.`;
            }
        }

        if (errorMsg) {
            showError(input, errorMsg);
            return false;
        }

        input.style.borderColor = '#10b981'; // Green for success
        return true;
    }

    function showError(input, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.cssText = 'color: #ef4444; font-size: 0.8rem; margin-top: 0.4rem; font-weight: 500;';

        input.style.borderColor = '#ef4444';
        input.parentElement.appendChild(errorDiv);
    }
});
