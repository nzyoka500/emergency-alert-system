/**
 * Emergency Alert System - Main JavaScript File
 * Handles form validation, interactions, and UI functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize application on page load
 */
function initializeApp() {
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize interactive elements
    initializeInteractiveElements();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Setup event listeners
    setupEventListeners();
    
    console.log('Emergency Alert System initialized');
}

/**
 * Form Validation
 */
function initializeFormValidation() {
    // Bootstrap form validation
    const forms = document.querySelectorAll('form');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Custom validation for specific fields
    setupCustomValidation();
}

/**
 * Setup custom form field validation
 */
function setupCustomValidation() {
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value && !isValidEmail(this.value)) {
                this.setCustomValidity('Please enter a valid email address');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    });

    // Phone validation
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value && !isValidPhone(this.value)) {
                this.setCustomValidity('Please enter a valid phone number');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    });

    // Password match validation
    const passwordInput = document.querySelector('input[name="password"]');
    const passwordConfirmInput = document.querySelector('input[name="password_confirm"]');
    
    if (passwordInput && passwordConfirmInput) {
        passwordConfirmInput.addEventListener('change', function() {
            if (this.value && this.value !== passwordInput.value) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    }
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate phone format
 */
function isValidPhone(phone) {
    const phoneRegex = /^[\d\s\-\+\(\)]{7,15}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipElements.forEach(element => {
        new bootstrap.Tooltip(element);
    });
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Search functionality
    setupSearch();
    
    // Alert actions
    setupAlertActions();
    
    // Status filter
    setupStatusFilter();
    
    // Form submissions
    setupFormSubmissions();
    
    // Modal actions
    setupModalActions();
}

/**
 * Setup search functionality
 */
function setupSearch() {
    const searchInputs = document.querySelectorAll('input[placeholder*="Search"]');
    
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            // Real-time filtering (optional - can be used for client-side filtering)
            const searchTerm = this.value.toLowerCase();
            
            // Log search for debugging
            console.log('Search term:', searchTerm);
            
            // Add visual feedback
            if (searchTerm.length > 0) {
                this.classList.add('is-active');
            } else {
                this.classList.remove('is-active');
            }
        });
    });
}

/**
 * Setup status filter actions
 */
function setupStatusFilter() {
    const statusCards = document.querySelectorAll('[style*="border-left: 4px solid"]');
    
    statusCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove active state from all cards
            statusCards.forEach(c => {
                c.style.opacity = '1';
            });
            
            // Add visual feedback to clicked card
            this.style.opacity = '0.8';
        });
    });
}

/**
 * Setup alert-related actions
 */
function setupAlertActions() {
    // View alert details
    const viewButtons = document.querySelectorAll('a[href*="alert-details.php"]');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            console.log('Viewing alert details');
        });
    });

    // Edit alert actions
    const editButtons = document.querySelectorAll('button[data-action="edit"]');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const alertId = this.dataset.alertId;
            editAlert(alertId);
        });
    });

    // Delete alert actions
    const deleteButtons = document.querySelectorAll('button[data-action="delete"]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const alertId = this.dataset.alertId;
            deleteAlert(alertId);
        });
    });

    // Update status actions
    const statusButtons = document.querySelectorAll('button[data-action="status"]');
    statusButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const alertId = this.dataset.alertId;
            const newStatus = this.dataset.status;
            updateAlertStatus(alertId, newStatus);
        });
    });
}

/**
 * Edit alert
 */
function editAlert(alertId) {
    console.log('Editing alert:', alertId);
    // Redirect to edit page or open modal
    window.location.href = 'edit-alert.php?id=' + alertId;
}

/**
 * Delete alert with confirmation
 */
function deleteAlert(alertId) {
    if (confirm('Are you sure you want to delete this alert? This action cannot be undone.')) {
        console.log('Deleting alert:', alertId);
        
        // Create form and submit to delete endpoint
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete-alert.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'alert_id';
        input.value = alertId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        
        // Show loading toast
        showToast('Deleting alert...', 'info');
    }
}

/**
 * Update alert status
 */
function updateAlertStatus(alertId, newStatus) {
    console.log('Updating alert', alertId, 'to status:', newStatus);
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'update-alert-status.php';
    
    const alertIdInput = document.createElement('input');
    alertIdInput.type = 'hidden';
    alertIdInput.name = 'alert_id';
    alertIdInput.value = alertId;
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = newStatus;
    
    form.appendChild(alertIdInput);
    form.appendChild(statusInput);
    document.body.appendChild(form);
    
    // Show loading toast
    showToast('Updating status...', 'info');
    
    form.submit();
}

/**
 * Setup form submissions
 */
function setupFormSubmissions() {
    // Login form
    const loginForm = document.querySelector('form[action="login-process.php"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (this.checkValidity() === false) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    // Register form
    const registerForm = document.querySelector('form[action="register-process.php"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (this.checkValidity() === false) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    // Alert creation form
    const alertForm = document.querySelector('form[action="create-alert-process.php"]');
    if (alertForm) {
        alertForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            // Show success message
            showToast('Alert created successfully!', 'success');
        });
    }
}

/**
 * Setup modal actions
 */
function setupModalActions() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        // Handle modal close
        const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                console.log('Modal closed');
            });
        });
    });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.setAttribute('role', 'alert');
    toast.style.cssText = `
        min-width: 300px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
    `;

    const messageSpan = document.createElement('span');
    messageSpan.textContent = message;

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.setAttribute('data-bs-dismiss', 'alert');
    closeButton.setAttribute('aria-label', 'Close');

    toast.appendChild(messageSpan);
    toast.appendChild(closeButton);
    toastContainer.appendChild(toast);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        const toastElement = document.getElementById(toastId);
        if (toastElement) {
            toastElement.remove();
        }
    }, 5000);
}

/**
 * Format date and time
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Convert timestamp to relative time (e.g., "2 hours ago")
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const secondsAgo = Math.floor((now - date) / 1000);

    let interval = secondsAgo / 31536000;
    if (interval > 1) {
        return Math.floor(interval) + ' years ago';
    }

    interval = secondsAgo / 2592000;
    if (interval > 1) {
        return Math.floor(interval) + ' months ago';
    }

    interval = secondsAgo / 86400;
    if (interval > 1) {
        return Math.floor(interval) + ' days ago';
    }

    interval = secondsAgo / 3600;
    if (interval > 1) {
        return Math.floor(interval) + ' hours ago';
    }

    interval = secondsAgo / 60;
    if (interval > 1) {
        return Math.floor(interval) + ' minutes ago';
    }

    return 'just now';
}

/**
 * Get URL parameters
 */
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

/**
 * Sanitize HTML input
 */
function sanitizeHTML(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Check if user is on mobile device
 */
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Show loading spinner
 */
function showLoadingSpinner() {
    let spinner = document.getElementById('global-spinner');
    if (!spinner) {
        spinner = document.createElement('div');
        spinner.id = 'global-spinner';
        spinner.innerHTML = `
            <div style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.3);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9998;
            ">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        document.body.appendChild(spinner);
    }
    spinner.style.display = 'flex';
}

/**
 * Hide loading spinner
 */
function hideLoadingSpinner() {
    const spinner = document.getElementById('global-spinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
}

/**
 * Debounce function for search/filter
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Export data to CSV
 */
function exportToCSV(data, filename = 'export.csv') {
    if (!data || data.length === 0) {
        showToast('No data to export', 'warning');
        return;
    }

    // Create CSV content
    let csv = '';
    const headers = Object.keys(data[0]);
    csv += headers.join(',') + '\n';

    data.forEach(row => {
        const values = headers.map(header => {
            const value = row[header];
            return '"' + (value || '').toString().replace(/"/g, '""') + '"';
        });
        csv += values.join(',') + '\n';
    });

    // Create blob and download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showToast('Data exported successfully', 'success');
}

function exportReportExcel(reportData, filename = 'responda-report.xlsx') {
    if (!window.XLSX) {
        showToast('Excel export library is not loaded.', 'danger');
        return;
    }

    const wb = XLSX.utils.book_new();
    const overviewRows = [
        { Metric: 'Total Alerts', Value: reportData.summary.total_alerts },
        { Metric: 'Resolved Alerts', Value: reportData.summary.resolved_alerts },
        { Metric: 'Resolution Rate', Value: reportData.summary.response_rate + '%' }
    ];

    const overviewSheet = XLSX.utils.json_to_sheet(overviewRows);
    XLSX.utils.book_append_sheet(wb, overviewSheet, 'Overview');

    if (reportData.typeStats && reportData.typeStats.length) {
        const typeSheet = XLSX.utils.json_to_sheet(reportData.typeStats.map(row => ({ Category: row.name, Count: row.count })));
        XLSX.utils.book_append_sheet(wb, typeSheet, 'Category Breakdown');
    }

    if (reportData.monthlyStats && reportData.monthlyStats.length) {
        const monthlySheet = XLSX.utils.json_to_sheet(reportData.monthlyStats.map(row => ({ Month: row.month, Count: row.count })));
        XLSX.utils.book_append_sheet(wb, monthlySheet, 'Monthly Trend');
    }

    XLSX.writeFile(wb, filename);
    showToast('Excel report downloaded successfully.', 'success');
}

function exportReportPdf(reportData, filename = 'responda-report.pdf') {
    const jsPDFLib = window.jspdf?.jsPDF || window.jsPDF;
    if (!jsPDFLib) {
        showToast('PDF export library is not loaded.', 'danger');
        return;
    }

    const doc = new jsPDFLib({ orientation: 'landscape', unit: 'pt', format: 'a4' });
    doc.setFontSize(18);
    doc.text('Responda Alert Report', 40, 40);
    doc.setFontSize(11);
    doc.text(`Total Alerts: ${reportData.summary.total_alerts}`, 40, 70);
    doc.text(`Resolved Alerts: ${reportData.summary.resolved_alerts}`, 40, 86);
    doc.text(`Resolution Rate: ${reportData.summary.response_rate}%`, 40, 102);

    const addChart = (chartId, x, y, width) => {
        const canvas = document.getElementById(chartId);
        if (!canvas || typeof canvas.toDataURL !== 'function') return;
        const imgData = canvas.toDataURL('image/png');
        doc.addImage(imgData, 'PNG', x, y, width, 140);
    };

    addChart('monthlyReportChart', 40, 130, 320);
    addChart('typeReportChart', 380, 130, 320);

    let lineY = 292;
    if (reportData.typeStats && reportData.typeStats.length) {
        doc.setFontSize(12);
        doc.text('Alert Category Breakdown', 40, lineY);
        lineY += 16;
        reportData.typeStats.forEach(row => {
            doc.setFontSize(10);
            doc.text(`${row.name}: ${row.count}`, 40, lineY);
            lineY += 14;
        });
        lineY += 6;
    }

    if (reportData.monthlyStats && reportData.monthlyStats.length) {
        doc.setFontSize(12);
        doc.text('Monthly Trend', 40, lineY);
        lineY += 16;
        reportData.monthlyStats.forEach(row => {
            doc.setFontSize(10);
            doc.text(`${row.month}: ${row.count}`, 40, lineY);
            lineY += 14;
        });
    }

    doc.save(filename);
    showToast('PDF report created successfully.', 'success');
}

/**
 * Print page
 */
function printPage(printAreaId = 'main-content') {
    const printContent = document.getElementById(printAreaId)?.innerHTML || document.body.innerHTML;
    const printWindow = window.open('', '', 'height=400,width=800');
    
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link rel="stylesheet" href="assets/css/styles.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    printWindow.print();
}

/**
 * Handle API errors
 */
function handleApiError(error) {
    console.error('API Error:', error);
    
    let message = 'An error occurred. Please try again.';
    
    if (error.response) {
        if (error.response.status === 401) {
            message = 'Session expired. Please login again.';
            window.location.href = 'index.php';
        } else if (error.response.status === 403) {
            message = 'You do not have permission to perform this action.';
        } else if (error.response.status === 404) {
            message = 'The requested resource was not found.';
        } else if (error.response.status === 500) {
            message = 'Server error. Please try again later.';
        }
    }
    
    showToast(message, 'danger');
}

/**
 * Local Storage utilities
 */
const StorageManager = {
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.error('Error saving to localStorage:', e);
        }
    },
    get: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('Error reading from localStorage:', e);
            return null;
        }
    },
    remove: function(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.error('Error removing from localStorage:', e);
        }
    },
    clear: function() {
        try {
            localStorage.clear();
        } catch (e) {
            console.error('Error clearing localStorage:', e);
        }
    }
};

/**
 * Export functions for global use
 */
window.AlertApp = {
    showToast,
    formatDateTime,
    timeAgo,
    getUrlParameter,
    sanitizeHTML,
    isMobileDevice,
    showLoadingSpinner,
    hideLoadingSpinner,
    debounce,
    exportToCSV,
    printPage,
    handleApiError,
    StorageManager
};

// Expose export functions globally for reports page
window.exportReportPdf = exportReportPdf;
window.exportReportExcel = exportReportExcel;
