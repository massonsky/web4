// Dress Rental Calculator

// Base prices (loaded from file in real implementation)
const PRICES = {
    baseRental: 500,
    baseService: 200,
    
    // Length surcharges
    length: {
        maxi: 100,
        maxi_train: 150,
        midi: 50
    },
    
    // Type surcharges
    type: {
        evening: 100,
        graduation: 10,
        business: 50
    },
    
    // Event multiplier
    eventMultiplier: 1.5,
    
    // Additional services
    services: {
        fitting: 80,
        steaming: 30
    }
};

// Descriptions for dress types
const DRESS_DESCRIPTIONS = {
    evening: "Элегантные вечерние платья для торжественных мероприятий, выполненные из премиальных тканей с изысканной отделкой.",
    graduation: "Стильные платья для выпускного вечера - яркие, молодежные модели, которые подчеркнут вашу индивидуальность.",
    business: "Строгие деловые костюмы для офиса и деловых встреч, выполненные в классическом стиле."
};

// Descriptions for rental types
const RENTAL_DESCRIPTIONS = {
    regular: "Стандартная аренда для повседневных случаев и небольших мероприятий.",
    event: "Премиум аренда для особых мероприятий с повышенным уровнем сервиса и гарантиями."
};

// Calculate total price
function calculatePrice() {
    const form = document.getElementById('rentalForm');
    const formData = new FormData(form);
    
    let totalPrice = PRICES.baseRental + PRICES.baseService;
    let breakdown = [];
    
    // Add base prices to breakdown
    breakdown.push(`Базовая аренда: ${PRICES.baseRental}₽`);
    breakdown.push(`Базовые услуги: ${PRICES.baseService}₽`);
    
    // Length surcharge
    const length = formData.get('dress_length');
    if (length && PRICES.length[length]) {
        totalPrice += PRICES.length[length];
        const lengthNames = {
            maxi: 'Макси',
            maxi_train: 'Макси со шлейфом',
            midi: 'Миди'
        };
        breakdown.push(`${lengthNames[length]}: +${PRICES.length[length]}₽`);
    }
    
    // Type surcharge
    const type = formData.get('dress_type');
    if (type && PRICES.type[type]) {
        totalPrice += PRICES.type[type];
        const typeNames = {
            evening: 'Вечернее платье',
            graduation: 'На выпускной',
            business: 'Деловой костюм'
        };
        breakdown.push(`${typeNames[type]}: +${PRICES.type[type]}₽`);
    }
    
    // Additional services
    const services = formData.getAll('services[]');
    services.forEach(service => {
        if (PRICES.services[service]) {
            totalPrice += PRICES.services[service];
            const serviceNames = {
                fitting: 'Подгонка по фигуре',
                steaming: 'Отпаривание'
            };
            breakdown.push(`${serviceNames[service]}: +${PRICES.services[service]}₽`);
        }
    });
    
    // Event multiplier
    const rentalType = formData.get('rental_type');
    if (rentalType === 'event') {
        const beforeMultiplier = totalPrice;
        totalPrice = Math.round(totalPrice * PRICES.eventMultiplier);
        breakdown.push(`Мероприятие (×${PRICES.eventMultiplier}): ${beforeMultiplier}₽ → ${totalPrice}₽`);
    }
    
    // Days multiplier
    const days = parseInt(formData.get('days')) || 1;
    if (days > 1) {
        totalPrice *= days;
        breakdown.push(`Количество дней (×${days}): ${totalPrice / days}₽ → ${totalPrice}₽`);
    }
    
    // Update display
    document.getElementById('totalPrice').textContent = `${totalPrice}₽`;
    document.getElementById('priceBreakdown').innerHTML = breakdown.join('<br>');
}

// Update dress type description
function updateDescription() {
    const form = document.getElementById('rentalForm');
    const formData = new FormData(form);
    const type = formData.get('dress_type');
    const descriptionElement = document.getElementById('dressTypeDescription');
    
    if (type && DRESS_DESCRIPTIONS[type]) {
        descriptionElement.textContent = DRESS_DESCRIPTIONS[type];
        descriptionElement.style.display = 'block';
    } else {
        descriptionElement.style.display = 'none';
    }
}

// Update rental type description
function updateRentalDescription() {
    const form = document.getElementById('rentalForm');
    const formData = new FormData(form);
    const rentalType = formData.get('rental_type');
    const descriptionElement = document.getElementById('rentalTypeDescription');
    
    if (rentalType && RENTAL_DESCRIPTIONS[rentalType]) {
        descriptionElement.textContent = RENTAL_DESCRIPTIONS[rentalType];
        descriptionElement.style.display = 'block';
    } else {
        descriptionElement.style.display = 'none';
    }
}

// Validation functions
function validateField(fieldName, value, fieldElement) {
    const errors = [];
    
    switch(fieldName) {
        case 'name':
            if (!value || value.trim().length < 2) {
                errors.push('Имя должно содержать минимум 2 символа');
            }
            if (value && !/^[а-яёА-ЯЁa-zA-Z\s-]+$/.test(value)) {
                errors.push('Имя может содержать только буквы, пробелы и дефисы');
            }
            break;
            
        case 'email':
            if (!value) {
                errors.push('Email обязателен для заполнения');
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                errors.push('Введите корректный email адрес');
            }
            break;
            
        case 'address':
            if (!value || value.trim().length < 5) {
                errors.push('Адрес должен содержать минимум 5 символов');
            }
            break;
            
        case 'days':
            const days = parseInt(value);
            if (!value || days < 1) {
                errors.push('Количество дней должно быть больше 0');
            } else if (days > 30) {
                errors.push('Максимальный срок аренды - 30 дней');
            }
            break;
            
        case 'dress_length':
            if (!value) {
                errors.push('Выберите длину платья');
            }
            break;
            
        case 'dress_type':
            if (!value) {
                errors.push('Выберите тип платья');
            }
            break;
    }
    
    return errors;
}

function showFieldError(fieldElement, errors) {
    // Remove existing error styling and messages
    clearFieldError(fieldElement);
    
    if (errors.length > 0) {
        // Add error styling
        fieldElement.classList.add('border-red-500', 'bg-red-900/20');
        fieldElement.classList.remove('border-gray-600', 'focus:border-pink-400');
        
        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error text-red-400 text-sm mt-1';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${errors.join(', ')}`;
        
        // Insert error message after the field
        fieldElement.parentNode.appendChild(errorDiv);
        
        return false;
    }
    
    return true;
}

function clearFieldError(fieldElement) {
    // Remove error styling
    fieldElement.classList.remove('border-red-500', 'bg-red-900/20');
    fieldElement.classList.add('border-gray-600');
    
    // Remove error message
    const existingError = fieldElement.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function validateForm() {
    const form = document.getElementById('rentalForm');
    const formData = new FormData(form);
    let isValid = true;
    
    // Define required fields with their elements
    const requiredFields = [
        { name: 'name', element: form.querySelector('input[name="name"]') },
        { name: 'email', element: form.querySelector('input[name="email"]') },
        { name: 'address', element: form.querySelector('input[name="address"]') },
        { name: 'days', element: form.querySelector('input[name="days"]') },
        { name: 'dress_length', element: form.querySelector('select[name="dress_length"]') },
        { name: 'dress_type', element: form.querySelector('select[name="dress_type"]') }
    ];
    
    // Validate each field
    requiredFields.forEach(field => {
        const value = formData.get(field.name);
        const errors = validateField(field.name, value, field.element);
        const fieldValid = showFieldError(field.element, errors);
        
        if (!fieldValid) {
            isValid = false;
        }
    });
    
    return isValid;
}

// Real-time validation
function setupRealTimeValidation() {
    const form = document.getElementById('rentalForm');
    const fields = form.querySelectorAll('input[required], select[required]');
    
    fields.forEach(field => {
        // Validate on blur (when user leaves the field)
        field.addEventListener('blur', function() {
            const value = this.value;
            const fieldName = this.name;
            const errors = validateField(fieldName, value, this);
            showFieldError(this, errors);
        });
        
        // Clear errors on focus
        field.addEventListener('focus', function() {
            clearFieldError(this);
        });
        
        // Validate on input for immediate feedback
        field.addEventListener('input', function() {
            // Only validate if field was previously marked as invalid
            if (this.classList.contains('border-red-500')) {
                const value = this.value;
                const fieldName = this.name;
                const errors = validateField(fieldName, value, this);
                showFieldError(this, errors);
            }
            
            // Recalculate price
            if (this.name === 'days') {
                calculatePrice();
            }
        });
    });
}

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('rentalForm');
    
    // Initialize descriptions
    updateRentalDescription();
    
    // Setup real-time validation
    setupRealTimeValidation();
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate entire form
        if (!validateForm()) {
            // Scroll to first error
            const firstError = form.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            
            // Show general error message
            showNotification('Пожалуйста, исправьте ошибки в форме', 'error');
            return;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Обработка...';
        
        // Submit form to backend
        fetch('order.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => {
            if (response.ok) {
                showNotification('Заказ успешно оформлен!', 'success');
                setTimeout(() => {
                    window.location.href = 'bill.php';
                }, 1500);
            } else {
                throw new Error('Server error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Ошибка при оформлении заказа. Попробуйте еще раз.', 'error');
        })
        .finally(() => {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // Initialize price calculation
    calculatePrice();
});

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
    
    // Set notification style based on type
    const styles = {
        success: 'bg-green-600 text-white border-l-4 border-green-400',
        error: 'bg-red-600 text-white border-l-4 border-red-400',
        info: 'bg-blue-600 text-white border-l-4 border-blue-400',
        warning: 'bg-yellow-600 text-white border-l-4 border-yellow-400'
    };
    
    notification.className += ` ${styles[type] || styles.info}`;
    
    // Set notification content
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle',
        warning: 'fas fa-exclamation-triangle'
    };
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="${icons[type] || icons.info} mr-3 text-lg"></i>
            <span class="flex-1">${message}</span>
            <button class="ml-3 text-white hover:text-gray-200 focus:outline-none" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
        notification.classList.add('translate-x-0');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Additional user experience enhancements
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('rentalForm');
    const inputs = document.querySelectorAll('input, select, textarea');
    
    // Prevent double submission
    let isSubmitting = false;
    form.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        isSubmitting = true;
        
        // Reset flag after 3 seconds as fallback
        setTimeout(() => {
            isSubmitting = false;
        }, 3000);
    });
    
    // Handle Enter key in form fields
    inputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                
                // Find next focusable element
                const focusableElements = Array.from(form.querySelectorAll(
                    'input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])'
                ));
                
                const currentIndex = focusableElements.indexOf(this);
                const nextElement = focusableElements[currentIndex + 1];
                
                if (nextElement) {
                    nextElement.focus();
                } else {
                    // If last element, submit form if valid
                    if (validateForm()) {
                        form.submit();
                    }
                }
            }
        });
        
        // Add floating animation
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('transform', 'scale-105');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('transform', 'scale-105');
        });
    });
    
    // Auto-save form data to localStorage
    const saveFormData = () => {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (key !== 'services[]') {
                data[key] = value;
            }
        }
        
        // Save services separately
        data.services = Array.from(form.querySelectorAll('input[name="services[]"]:checked'))
            .map(cb => cb.value);
        
        localStorage.setItem('rentalFormData', JSON.stringify(data));
    };
    
    // Load saved form data
    const loadFormData = () => {
        const savedData = localStorage.getItem('rentalFormData');
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                
                Object.keys(data).forEach(key => {
                    if (key === 'services') {
                        // Handle services checkboxes
                        data[key].forEach(service => {
                            const checkbox = form.querySelector(`input[name="services[]"][value="${service}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                    } else {
                        const field = form.querySelector(`[name="${key}"]`);
                        if (field) field.value = data[key];
                    }
                });
                
                // Recalculate price and update descriptions
                calculatePrice();
                updateRentalDescription();
            } catch (e) {
                console.log('Error loading saved form data:', e);
            }
        }
    };
    
    // Save form data on input changes
    inputs.forEach(input => {
        input.addEventListener('input', saveFormData);
        input.addEventListener('change', saveFormData);
    });
    
    // Load saved data on page load
    loadFormData();
    
    // Clear saved data on successful submission
    form.addEventListener('submit', function() {
        if (validateForm()) {
            localStorage.removeItem('rentalFormData');
        }
    });
    
    // Add tooltips for better UX
    const addTooltips = () => {
        const tooltipData = {
            'name': 'Введите ваше полное имя как в документе',
            'email': 'Мы отправим подтверждение заказа на этот email',
            'address': 'Укажите точный адрес для доставки платья',
            'days': 'Минимум 1 день, максимум 30 дней',
            'dress_length': 'Выберите подходящую длину платья',
            'dress_type': 'Тип платья влияет на итоговую стоимость'
        };
        
        Object.keys(tooltipData).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.setAttribute('title', tooltipData[fieldName]);
            }
        });
    };
    
    addTooltips();
});