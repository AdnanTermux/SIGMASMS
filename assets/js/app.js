// Sigma SMS A2P — App JS

// Global Select2 init helper
function initSelect2(selector, opts) {
    $(selector).select2(Object.assign({ theme: 'bootstrap-5', width: '100%' }, opts || {}));
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    });
}

// Toast notification
function showToast(msg, type) {
    type = type || 'info';
    const colors = { success: '#198754', danger: '#dc3545', info: '#0d6efd', warning: '#fd7e14' };
    const toast = document.createElement('div');
    toast.style.cssText = `position:fixed;bottom:20px;right:20px;z-index:9999;padding:.65rem 1.2rem;border-radius:8px;background:${colors[type]||colors.info};color:#fff;font-size:.875rem;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.2);transition:opacity .3s;`;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// AJAX helper with CSRF
function ajaxPost(url, data, cb) {
    data.csrf_token = document.querySelector('meta[name="csrf"]')?.content || '';
    $.post(url, data, cb).fail(function(xhr) {
        showToast('Request failed: ' + (xhr.responseJSON?.error || 'Unknown error'), 'danger');
    });
}

$(document).ready(function() {
    // Initialize all select2 on page
    if ($.fn.select2) {
        $('select.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // Flatpickr date ranges
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.flatpickr-date', { dateFormat: 'Y-m-d' });
        flatpickr('.flatpickr-datetime', { enableTime: true, dateFormat: 'Y-m-d H:i' });
    }

    // DataTable default settings
    if ($.fn.DataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                paginate: { previous: '&lsaquo;', next: '&rsaquo;' },
                search: '',
                searchPlaceholder: 'Search…',
                lengthMenu: '_MENU_ per page',
            },
            dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'B>><'row'<'col-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                { extend: 'copy',   className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'csv',    className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'excel',  className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'pdf',    className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'print',  className: 'btn btn-sm btn-outline-secondary' },
            ],
        });
    }
});
