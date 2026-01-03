/**
 * =============================================
 * CUSTOM JAVASCRIPT
 * Sistem Pemantauan Kualitas Udara
 * =============================================
 */

// Document Ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistem Pemantauan Kualitas Udara - Ready');
    
    // Initialize tooltips (Bootstrap 5)
    initTooltips();
    
    // Add fade-in animation to cards
    animateCards();
    
    // Initialize notification checker
    checkNotifications();


});

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => 
        new bootstrap.Tooltip(tooltipTriggerEl)
    );
}

/**
 * Animate cards on page load
 */
function animateCards() {
    const cards = document.querySelectorAll('.card, .location-card, .stat-card');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

/**
 * Check for active alerts and show notification
 */
function checkNotifications() {
    const alertBadge = document.querySelector('.navbar .badge.bg-danger');
    
    if (alertBadge && parseInt(alertBadge.textContent) > 0) {
        // Show browser notification if permitted
        if ("Notification" in window && Notification.permission === "granted") {
            showNotification(
                'Peringatan Kualitas Udara!',
                `Ada ${alertBadge.textContent} peringatan aktif. Cek segera!`
            );
        } else if ("Notification" in window && Notification.permission !== "denied") {
            // Request permission
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    showNotification(
                        'Peringatan Kualitas Udara!',
                        `Ada ${alertBadge.textContent} peringatan aktif. Cek segera!`
                    );
                }
            });
        }
    }
}

/**
 * Show browser notification
 */
function showNotification(title, body) {
    const notification = new Notification(title, {
        body: body,
        icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23dc3545"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
        badge: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23dc3545"><circle cx="12" cy="12" r="10"/></svg>'
    });
    
    notification.onclick = function() {
        window.focus();
        window.location.href = 'alert.php';
        notification.close();
    };
}

/**
 * Format number with thousand separator
 */
function formatNumber(number, decimals = 0) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

/**
 * Get AQI color based on value
 */
function getAQIColor(aqi) {
    if (aqi <= 50) return '#198754'; // Green - Good
    if (aqi <= 100) return '#0d6efd'; // Blue - Moderate
    if (aqi <= 150) return '#ffc107'; // Yellow - Unhealthy for Sensitive Groups
    if (aqi <= 200) return '#dc3545'; // Red - Unhealthy
    if (aqi <= 300) return '#6f42c1'; // Purple - Very Unhealthy
    return '#212529'; // Maroon - Hazardous
}

/**
 * Get status text based on AQI
 */
function getAQIStatus(aqi) {
    if (aqi <= 50) return 'Baik';
    if (aqi <= 100) return 'Sedang';
    if (aqi <= 150) return 'Tidak Sehat untuk Kelompok Sensitif';
    if (aqi <= 200) return 'Tidak Sehat';
    if (aqi <= 300) return 'Sangat Tidak Sehat';
    return 'Berbahaya';
}

/**
 * Test API Endpoint
 * Fungsi untuk mengirim data dummy ke API
 */
function testAPIEndpoint() {
    const testData = {
        lokasi: "Test Location",
        pm25: 45.5,
        pm10: 78.2,
        co: 2.3,
        no2: 0.08,
        so2: 0.05,
        o3: 0.06,
        suhu: 32.5,
        kelembapan: 68.0
    };
    
    fetch('api/post_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(testData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('API Test Response:', data);
        if (data.status === 'success') {
            alert('✅ Data berhasil dikirim!\nAQI: ' + data.data.aqi + '\nStatus: ' + data.data.status_kualitas);
        } else {
            alert('❌ Gagal mengirim data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('API Error:', error);
        alert('❌ Error: ' + error.message);
    });
}

/**
 * Show loading overlay
 */
function showLoading() {
    const loadingHTML = `
        <div class="loading-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', loadingHTML);
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastHTML = `
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.querySelector('.toast:last-of-type');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove after shown
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.parentElement.remove();
    });
}

/**
 * Format date to Indonesian format
 */
function formatDateIndonesia(dateString) {
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const date = new Date(dateString);
    const day = date.getDate();
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${day} ${month} ${year} ${hours}:${minutes}`;
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Text berhasil disalin!', 'success');
    }).catch(err => {
        showToast('Gagal menyalin text', 'danger');
        console.error('Copy failed:', err);
    });
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename = 'data.csv') {
    const table = document.getElementById(tableId);
    if (!table) {
        showToast('Tabel tidak ditemukan', 'danger');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let row of rows) {
        let cols = row.querySelectorAll('td, th');
        let csvRow = [];
        for (let col of cols) {
            csvRow.push('"' + col.innerText.replace(/"/g, '""') + '"');
        }
        csv.push(csvRow.join(','));
    }
    
    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    
    showToast('Data berhasil diekspor!', 'success');
}

/**
 * Validate form data
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    return isValid;
}

/**
 * Smooth scroll to element
 */
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Toggle dark mode (optional feature)
 */
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
}

// Load dark mode preference
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
}

/**
 * Debug mode
 */
const DEBUG = false;

function debug(...args) {
    if (DEBUG) {
        console.log('[DEBUG]', ...args);
    }
}

// Export functions to window object
window.testAPIEndpoint = testAPIEndpoint;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showToast = showToast;
window.formatDateIndonesia = formatDateIndonesia;
window.copyToClipboard = copyToClipboard;
window.exportTableToCSV = exportTableToCSV;
window.validateForm = validateForm;
window.smoothScrollTo = smoothScrollTo;
window.toggleDarkMode = toggleDarkMode;
window.getAQIColor = getAQIColor;
window.getAQIStatus = getAQIStatus;

console.log('✅ JavaScript loaded successfully');