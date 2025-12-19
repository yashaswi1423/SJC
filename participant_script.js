// Participant Registration Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('participantForm');
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    // Mobile navigation toggle
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            const isOpen = navMenu.classList.contains('open');
            navMenu.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', !isOpen);
        });
    }

    // Form validation rules
    const validationRules = {
        domain: {
            required: true,
            message: 'Please select a domain'
        },
        teamName: {
            required: true,
            minLength: 2,
            message: 'Team name must be at least 2 characters long'
        },
        leadUsn: {
            required: true,
            pattern: /^[A-Za-z0-9]{8,15}$/,
            message: 'USN/ID must be 8-15 alphanumeric characters'
        },
        leadName: {
            required: true,
            minLength: 2,
            pattern: /^[A-Za-z\s.]+$/,
            message: 'Name must contain only letters, spaces, and dots'
        },
        leadGender: {
            required: true,
            message: 'Please select gender'
        },
        leadMobile: {
            required: true,
            pattern: /^[6-9]\d{9}$/,
            message: 'Mobile number must be 10 digits starting with 6-9'
        },
        leadEmail: {
            required: true,
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        collegeName: {
            required: true,
            minLength: 2,
            message: 'College name must be at least 2 characters long'
        },
        member2Usn: {
            required: true,
            pattern: /^[A-Za-z0-9]{8,15}$/,
            message: 'USN/ID must be 8-15 alphanumeric characters'
        },
        member2Name: {
            required: true,
            minLength: 2,
            pattern: /^[A-Za-z\s.]+$/,
            message: 'Name must contain only letters, spaces, and dots'
        },
        member2Gender: {
            required: true,
            message: 'Please select gender'
        },
        member2Mobile: {
            required: true,
            pattern: /^[6-9]\d{9}$/,
            message: 'Mobile number must be 10 digits starting with 6-9'
        },
        member3Usn: {
            required: true,
            pattern: /^[A-Za-z0-9]{8,15}$/,
            message: 'USN/ID must be 8-15 alphanumeric characters'
        },
        member3Name: {
            required: true,
            minLength: 2,
            pattern: /^[A-Za-z\s.]+$/,
            message: 'Name must contain only letters, spaces, and dots'
        },
        member3Gender: {
            required: true,
            message: 'Please select gender'
        },
        member3Mobile: {
            required: true,
            pattern: /^[6-9]\d{9}$/,
            message: 'Mobile number must be 10 digits starting with 6-9'
        },
        member4Usn: {
            required: true,
            pattern: /^[A-Za-z0-9]{8,15}$/,
            message: 'USN/ID must be 8-15 alphanumeric characters'
        },
        member4Name: {
            required: true,
            minLength: 2,
            pattern: /^[A-Za-z\s.]+$/,
            message: 'Name must contain only letters, spaces, and dots'
        },
        member4Gender: {
            required: true,
            message: 'Please select gender'
        },
        member4Mobile: {
            required: true,
            pattern: /^[6-9]\d{9}$/,
            message: 'Mobile number must be 10 digits starting with 6-9'
        },
        accommodation: {
            required: true,
            message: 'Please select accommodation requirement'
        }
    };

    // Validation functions
    function validateField(fieldName, value) {
        const rules = validationRules[fieldName];
        if (!rules) return { isValid: true };

        // Check required
        if (rules.required && (!value || value.trim() === '')) {
            return { isValid: false, message: rules.message };
        }

        // If field is not required and empty, it's valid
        if (!rules.required && (!value || value.trim() === '')) {
            return { isValid: true };
        }

        // Check minimum length
        if (rules.minLength && value.length < rules.minLength) {
            return { isValid: false, message: rules.message };
        }

        // Check pattern
        if (rules.pattern && !rules.pattern.test(value)) {
            return { isValid: false, message: rules.message };
        }

        return { isValid: true };
    }

    function validateRadioGroup(groupName) {
        const radios = document.querySelectorAll(`input[name="${groupName}"]`);
        const rules = validationRules[groupName];
        
        if (!rules) return { isValid: true };

        const isChecked = Array.from(radios).some(radio => radio.checked);
        
        if (rules.required && !isChecked) {
            return { isValid: false, message: rules.message };
        }

        return { isValid: true };
    }

    function showError(fieldName, message) {
        const errorElement = document.getElementById(`${fieldName}-error`);
        const field = document.getElementById(fieldName) || document.querySelector(`input[name="${fieldName}"]`);
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }
        
        if (field) {
            if (field.type === 'radio') {
                // For radio groups, add error class to all radios in the group
                const radios = document.querySelectorAll(`input[name="${fieldName}"]`);
                radios.forEach(radio => radio.classList.add('error'));
            } else {
                field.classList.add('error');
            }
        }
    }

    function clearError(fieldName) {
        const errorElement = document.getElementById(`${fieldName}-error`);
        const field = document.getElementById(fieldName) || document.querySelector(`input[name="${fieldName}"]`);
        
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.remove('show');
        }
        
        if (field) {
            if (field.type === 'radio') {
                // For radio groups, remove error class from all radios in the group
                const radios = document.querySelectorAll(`input[name="${fieldName}"]`);
                radios.forEach(radio => radio.classList.remove('error'));
            } else {
                field.classList.remove('error');
            }
        }
    }

    // Real-time validation
    function setupRealTimeValidation() {
        // Text inputs and selects
        const textFields = ['domain', 'teamName', 'leadUsn', 'leadName', 'leadMobile', 'leadEmail', 'collegeName',
                           'member2Usn', 'member2Name', 'member2Mobile', 'member3Usn', 'member3Name', 'member3Mobile',
                           'member4Usn', 'member4Name', 'member4Mobile', 'member5Usn', 'member5Name'];
        
        textFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('blur', function() {
                    const validation = validateField(fieldName, this.value);
                    if (!validation.isValid) {
                        showError(fieldName, validation.message);
                    } else {
                        clearError(fieldName);
                    }
                });

                field.addEventListener('input', function() {
                    // Clear error on input if field was previously invalid
                    if (this.classList.contains('error')) {
                        clearError(fieldName);
                    }
                });
            }
        });

        // Radio groups
        const radioGroups = ['leadGender', 'member2Gender', 'member3Gender', 'member4Gender', 'member5Gender', 'accommodation'];
        
        radioGroups.forEach(groupName => {
            const radios = document.querySelectorAll(`input[name="${groupName}"]`);
            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    clearError(groupName);
                });
            });
        });
    }

    // Validate member 5 fields together (if any field is filled, validate all filled fields)
    function validateMember5Fields() {
        const member5Usn = document.getElementById('member5Usn').value.trim();
        const member5Name = document.getElementById('member5Name').value.trim();
        const member5Gender = document.querySelector('input[name="member5Gender"]:checked');

        const hasAnyMember5Data = member5Usn || member5Name || member5Gender;

        if (hasAnyMember5Data) {
            let isValid = true;

            // If any field is filled, validate the filled fields
            if (member5Usn) {
                const usnValidation = validateField('member5Usn', member5Usn);
                if (!usnValidation.isValid) {
                    showError('member5Usn', 'USN/ID must be 8-15 alphanumeric characters');
                    isValid = false;
                }
            }

            if (member5Name) {
                const nameValidation = validateField('member5Name', member5Name);
                if (!nameValidation.isValid) {
                    showError('member5Name', 'Name must contain only letters, spaces, and dots');
                    isValid = false;
                }
            }

            return isValid;
        }

        return true; // Valid if no member 5 data is provided
    }

    // Check for duplicate USNs
    function validateUniqueUSNs() {
        const usnFields = ['leadUsn', 'member2Usn', 'member3Usn', 'member4Usn', 'member5Usn'];
        const usns = [];
        const duplicates = [];

        usnFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            const value = field.value.trim().toLowerCase();
            
            if (value) {
                if (usns.includes(value)) {
                    duplicates.push(fieldName);
                } else {
                    usns.push(value);
                }
            }
        });

        // Clear previous duplicate errors
        usnFields.forEach(fieldName => {
            const errorElement = document.getElementById(`${fieldName}-error`);
            if (errorElement && errorElement.textContent.includes('duplicate')) {
                clearError(fieldName);
            }
        });

        // Show duplicate errors
        duplicates.forEach(fieldName => {
            showError(fieldName, 'USN/ID must be unique for each team member');
        });

        return duplicates.length === 0;
    }

    // Check for duplicate mobile numbers
    function validateUniqueMobiles() {
        const mobileFields = ['leadMobile', 'member2Mobile', 'member3Mobile', 'member4Mobile'];
        const mobiles = [];
        const duplicates = [];

        mobileFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            const value = field.value.trim();
            
            if (value) {
                if (mobiles.includes(value)) {
                    duplicates.push(fieldName);
                } else {
                    mobiles.push(value);
                }
            }
        });

        // Clear previous duplicate errors
        mobileFields.forEach(fieldName => {
            const errorElement = document.getElementById(`${fieldName}-error`);
            if (errorElement && errorElement.textContent.includes('duplicate')) {
                clearError(fieldName);
            }
        });

        // Show duplicate errors
        duplicates.forEach(fieldName => {
            showError(fieldName, 'Mobile number must be unique for each team member');
        });

        return duplicates.length === 0;
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isFormValid = true;

        // Validate all required fields
        Object.keys(validationRules).forEach(fieldName => {
            let validation;
            
            if (fieldName.includes('Gender') || fieldName === 'accommodation') {
                validation = validateRadioGroup(fieldName);
            } else {
                const field = document.getElementById(fieldName);
                validation = validateField(fieldName, field ? field.value : '');
            }

            if (!validation.isValid) {
                showError(fieldName, validation.message);
                isFormValid = false;
            }
        });

        // Validate member 5 fields
        if (!validateMember5Fields()) {
            isFormValid = false;
        }

        // Validate unique USNs
        if (!validateUniqueUSNs()) {
            isFormValid = false;
        }

        // Validate unique mobile numbers
        if (!validateUniqueMobiles()) {
            isFormValid = false;
        }

        if (isFormValid) {
            // Show success message
            showSuccessMessage();
        } else {
            // Scroll to first error
            const firstError = document.querySelector('.error-message.show');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Form reset
    form.addEventListener('reset', function() {
        // Clear all errors
        Object.keys(validationRules).forEach(fieldName => {
            clearError(fieldName);
        });
        
        // Clear member 5 errors
        clearError('member5Usn');
        clearError('member5Name');
        clearError('member5Gender');
    });

    function showSuccessMessage() {
        const formContainer = document.querySelector('.form-container');
        
        formContainer.innerHTML = `
            <div class="success-message">
                <div class="success-icon">✓</div>
                <h3>Registration Successful!</h3>
                <p>Thank you for registering for Silver Spectrum TechFest 2025. Your team registration has been submitted successfully.</p>
                <p>You will receive a confirmation email shortly with further details about the hackathon.</p>
                <div class="form-actions">
                    <a href="index.html" class="btn primary">Back to Home</a>
                    <button type="button" class="btn outline" onclick="location.reload()">Register Another Team</button>
                </div>
            </div>
        `;
        
        // Scroll to success message
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Initialize real-time validation
    setupRealTimeValidation();

    // Auto-format mobile numbers (remove non-digits)
    const mobileFields = document.querySelectorAll('input[type="tel"]');
    mobileFields.forEach(field => {
        field.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    });

    // Auto-format USN fields (remove special characters except alphanumeric)
    const usnFields = document.querySelectorAll('input[id$="Usn"]');
    usnFields.forEach(field => {
        field.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0, 15);
        });
    });

    // Auto-format name fields (only letters, spaces, and dots)
    const nameFields = document.querySelectorAll('input[id$="Name"]');
    nameFields.forEach(field => {
        field.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Za-z\s.]/g, '');
        });
    });
});