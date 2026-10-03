document.addEventListener('DOMContentLoaded', () => {
    const customConfirmModal = document.getElementById('custom-confirm-modal');
    const customConfirmOverlay = document.getElementById('custom-confirm-overlay');
    const customConfirmDialog = document.getElementById('custom-confirm-dialog');
    const customConfirmMessage = document.getElementById('custom-confirm-message');
    const btnCancel = document.getElementById('custom-confirm-cancel');
    const btnOk = document.getElementById('custom-confirm-ok');
    
    if (!customConfirmModal) return;

    let confirmAction = null;

    function showCustomConfirm(message, onConfirm) {
        customConfirmMessage.textContent = message;
        confirmAction = onConfirm;
        
        customConfirmModal.classList.remove('hidden');
        customConfirmModal.classList.add('flex');
        
        // Animasi masuk
        setTimeout(() => {
            customConfirmOverlay.classList.remove('opacity-0');
            customConfirmDialog.classList.remove('scale-95', 'opacity-0');
            customConfirmDialog.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function hideCustomConfirm() {
        customConfirmOverlay.classList.add('opacity-0');
        customConfirmDialog.classList.remove('scale-100', 'opacity-100');
        customConfirmDialog.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            customConfirmModal.classList.add('hidden');
            customConfirmModal.classList.remove('flex');
            confirmAction = null;
        }, 300);
    }

    btnCancel.addEventListener('click', hideCustomConfirm);
    customConfirmOverlay.addEventListener('click', hideCustomConfirm);

    btnOk.addEventListener('click', () => {
        if (confirmAction) confirmAction();
        hideCustomConfirm();
    });

    // Delegasi event untuk data-confirm
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-confirm]');
        if (!el) return;
        
        e.preventDefault(); 
        
        var msg = el.getAttribute('data-confirm') || 'Apakah Anda yakin ingin melanjutkan aksi ini?';
        
        showCustomConfirm(msg, () => {
            if (el.tagName.toLowerCase() === 'a') {
                window.location.href = el.href;
            } else if (el.tagName.toLowerCase() === 'button' && el.type === 'submit') {
                const form = el.closest('form');
                if (form) form.submit();
            } else if (el.closest('form')) {
                el.closest('form').submit();
            }
        });
    });
});
