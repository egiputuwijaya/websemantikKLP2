<?php
/**
 * layouts/admin/modal_confirm.php
 * Komponen modal konfirmasi global untuk aksi-aksi penting.
 */
?>
<!-- Custom Confirm Modal HTML -->
<div id="custom-confirm-modal" class="fixed inset-0 z-[999] hidden items-center justify-center">
    <!-- Overlay -->
    <div id="custom-confirm-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Dialog -->
    <div id="custom-confirm-dialog" class="relative bg-white rounded-md shadow-xl w-full max-w-sm mx-4 transform scale-95 opacity-0 transition-all duration-300 flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900 font-heading">Konfirmasi</h3>
            </div>
        </div>
        
        <!-- Body -->
        <div class="p-5">
            <p id="custom-confirm-message" class="text-slate-600 text-sm leading-relaxed"></p>
        </div>
        
        <!-- Footer -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
            <button id="custom-confirm-cancel" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-md hover:bg-slate-50 focus:ring-4 focus:ring-slate-100 transition-colors">
                Batal
            </button>
            <button id="custom-confirm-ok" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-md hover:bg-red-700 focus:ring-4 focus:ring-red-100 transition-colors">
                Lanjutkan
            </button>
        </div>
    </div>
</div>
