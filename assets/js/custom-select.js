document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('select:not([data-no-custom])');
    
    selects.forEach(select => {
        // Sembunyikan select asli
        select.style.display = 'none';
        
        // Buat bungkus utama
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        
        // Copy margin/width classes if needed
        if (select.classList.contains('sm:w-64')) {
            wrapper.classList.add('sm:w-64');
        }
        if (select.classList.contains('mb-1.5')) {
            wrapper.classList.add('mb-1.5');
        }

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        
        // Buat trigger (tombol pembuka dropdown)
        const trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger';
        trigger.tabIndex = 0;
        
        const triggerText = document.createElement('span');
        triggerText.className = 'truncate block';
        
        const triggerIcon = document.createElement('i');
        triggerIcon.className = 'fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200';
        
        trigger.appendChild(triggerText);
        trigger.appendChild(triggerIcon);
        wrapper.appendChild(trigger);
        
        // Buat wadah opsi
        const optionsContainer = document.createElement('div');
        optionsContainer.className = 'custom-select-options';
        wrapper.appendChild(optionsContainer);
        
        // Fungsi untuk mengisi dan memperbarui opsi
        const updateOptions = () => {
            optionsContainer.innerHTML = '';
            let selectedText = '';
            
            Array.from(select.options).forEach((option, index) => {
                const optionEl = document.createElement('div');
                optionEl.className = 'custom-select-option';
                optionEl.textContent = option.textContent;
                
                // Tambahkan padding kiri ekstra jika ini adalah child/indented option
                if (option.textContent.startsWith('-- ')) {
                    optionEl.classList.add('text-slate-500', 'italic');
                }
                
                if (option.selected) {
                    optionEl.classList.add('selected');
                    selectedText = option.textContent;
                }
                
                optionEl.addEventListener('click', (e) => {
                    e.stopPropagation();
                    select.selectedIndex = index;
                    triggerText.textContent = option.textContent;
                    
                    Array.from(optionsContainer.children).forEach(c => c.classList.remove('selected'));
                    optionEl.classList.add('selected');
                    
                    wrapper.classList.remove('open');
                    triggerIcon.classList.remove('rotate-180');
                    
                    // Picu event change agar form atau JS lain tahu
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });
                
                optionsContainer.appendChild(optionEl);
            });
            
            triggerText.textContent = selectedText || (select.options[0] ? select.options[0].textContent : '');
        };
        
        updateOptions();
        
        // Toggle dropdown saat di-klik
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = wrapper.classList.contains('open');
            
            // Tutup semua dropdown lain yang sedang terbuka
            document.querySelectorAll('.custom-select-wrapper').forEach(w => {
                w.classList.remove('open');
                w.querySelector('.fa-chevron-down').classList.remove('rotate-180');
            });
            
            if (!isOpen) {
                wrapper.classList.add('open');
                triggerIcon.classList.add('rotate-180');
                
                // Gulir otomatis ke opsi yang terpilih
                const selected = optionsContainer.querySelector('.selected');
                if (selected) {
                    optionsContainer.scrollTop = selected.offsetTop - optionsContainer.clientHeight / 2 + selected.clientHeight / 2;
                }
            }
        });
        
        // Dukungan Keyboard
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                trigger.click();
            } else if (e.key === 'Escape') {
                wrapper.classList.remove('open');
                triggerIcon.classList.remove('rotate-180');
                trigger.focus();
            }
        });
        
        // Tutup dropdown jika klik di luar
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                wrapper.classList.remove('open');
                triggerIcon.classList.remove('rotate-180');
            }
        });
        
        // Dengarkan jika ada perubahan pada select asli (misal oleh script lain)
        select.addEventListener('change', () => {
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt) {
                triggerText.textContent = selectedOpt.textContent;
                Array.from(optionsContainer.children).forEach((c, idx) => {
                    if (idx === select.selectedIndex) c.classList.add('selected');
                    else c.classList.remove('selected');
                });
            }
        });
    });
});
