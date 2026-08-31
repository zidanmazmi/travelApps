(() => {
    'use strict';

    const wrapper = document.getElementById('adminWrapper');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('adminSidebarClose');
    const sidebarOverlay = document.getElementById('adminSidebarOverlay');
    const sidebarCloseTriggers = document.querySelectorAll('[data-admin-sidebar-close]');
    const desktopQuery = window.matchMedia('(min-width: 1200px)');
    const sidebarStorageKey = 'wl-admin-sidebar-mini';

    const isDesktop = () => desktopQuery.matches;
    let lastSidebarTrigger = null;

    const syncSidebarAccessibility = (isOpen = false) => {
        const drawerMode = !isDesktop();

        sidebarToggle?.setAttribute('aria-expanded', drawerMode && isOpen ? 'true' : 'false');
        sidebarToggle?.setAttribute(
            'aria-label',
            drawerMode
                ? (isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi')
                : 'Kecilkan atau lebarkan menu navigasi'
        );

        if (drawerMode) {
            document.getElementById('adminSidebar')?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        } else {
            document.getElementById('adminSidebar')?.removeAttribute('aria-hidden');
        }
    };

    const closeMobileSidebar = ({ restoreFocus = false } = {}) => {
        wrapper?.classList.remove('sidebar-open');
        document.body.classList.remove('admin-menu-lock');
        syncSidebarAccessibility(false);

        if (restoreFocus && lastSidebarTrigger instanceof HTMLElement) {
            lastSidebarTrigger.focus();
        }
    };

    const openMobileSidebar = () => {
        lastSidebarTrigger = document.activeElement;
        wrapper?.classList.add('sidebar-open');
        document.body.classList.add('admin-menu-lock');
        syncSidebarAccessibility(true);
        window.setTimeout(() => sidebarClose?.focus(), 120);
    };

    window.closeAdminSidebar = () => closeMobileSidebar({ restoreFocus: true });

    const applySavedSidebar = () => {
        if (!wrapper) return;

        closeMobileSidebar();

        if (isDesktop()) {
            const isMini = localStorage.getItem(sidebarStorageKey) === '1';
            wrapper.classList.toggle('sidebar-mini', isMini);
        } else {
            wrapper.classList.remove('sidebar-mini');
        }

        syncSidebarAccessibility(false);
    };

    sidebarToggle?.addEventListener('click', () => {
        if (!wrapper) return;

        if (isDesktop()) {
            const isMini = wrapper.classList.toggle('sidebar-mini');
            localStorage.setItem(sidebarStorageKey, isMini ? '1' : '0');
        } else if (wrapper.classList.contains('sidebar-open')) {
            closeMobileSidebar({ restoreFocus: true });
        } else {
            openMobileSidebar();
        }
    });

    sidebarCloseTriggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            closeMobileSidebar({ restoreFocus: true });
        });
    });
    desktopQuery.addEventListener?.('change', applySavedSidebar);
    applySavedSidebar();

    document.querySelectorAll('.admin-menu-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (!isDesktop()) closeMobileSidebar();
        });
    });

    /* ================= Command palette ================= */
    const commandModalElement = document.getElementById('adminCommandModal');
    const commandSearch = document.getElementById('adminCommandSearch');
    const commandItems = Array.from(document.querySelectorAll('.admin-command-item'));
    const commandEmpty = document.getElementById('adminCommandEmpty');
    let commandModal = null;
    let highlightedIndex = -1;

    if (commandModalElement && window.bootstrap) {
        commandModal = bootstrap.Modal.getOrCreateInstance(commandModalElement);

        commandModalElement.addEventListener('shown.bs.modal', () => {
            commandSearch?.focus();
            commandSearch?.select();
        });

        commandModalElement.addEventListener('hidden.bs.modal', () => {
            if (commandSearch) commandSearch.value = '';
            filterCommands('');
        });
    }

    const getVisibleCommandItems = () => commandItems.filter((item) => !item.hidden);

    const highlightCommand = (index) => {
        const visibleItems = getVisibleCommandItems();
        commandItems.forEach((item) => item.classList.remove('is-highlighted'));

        if (visibleItems.length === 0) {
            highlightedIndex = -1;
            return;
        }

        highlightedIndex = Math.max(0, Math.min(index, visibleItems.length - 1));
        visibleItems[highlightedIndex].classList.add('is-highlighted');
        visibleItems[highlightedIndex].scrollIntoView({ block: 'nearest' });
    };

    function filterCommands(query) {
        const keyword = query.trim().toLowerCase();
        let visible = 0;

        commandItems.forEach((item) => {
            const haystack = `${item.textContent} ${item.dataset.commandKeywords || ''}`.toLowerCase();
            const match = keyword === '' || haystack.includes(keyword);
            item.hidden = !match;
            if (match) visible += 1;
        });

        if (commandEmpty) commandEmpty.hidden = visible > 0;
        highlightedIndex = -1;
        commandItems.forEach((item) => item.classList.remove('is-highlighted'));
    }

    commandSearch?.addEventListener('input', (event) => filterCommands(event.target.value));

    commandSearch?.addEventListener('keydown', (event) => {
        const visibleItems = getVisibleCommandItems();

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            highlightCommand(highlightedIndex + 1);
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            highlightCommand(highlightedIndex <= 0 ? visibleItems.length - 1 : highlightedIndex - 1);
        }

        if (event.key === 'Enter' && visibleItems.length > 0) {
            event.preventDefault();
            const target = visibleItems[highlightedIndex >= 0 ? highlightedIndex : 0];
            window.location.href = target.href;
        }
    });

    document.addEventListener('keydown', (event) => {
        const isCommandShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';

        if (isCommandShortcut) {
            event.preventDefault();
            commandModal?.show();
        }

        if (event.key === 'Escape' && wrapper?.classList.contains('sidebar-open')) {
            closeMobileSidebar({ restoreFocus: true });
        }
    });

    /* ================= Bulk selection ================= */
    const bulkScopes = document.querySelectorAll('[data-bulk-scope]');

    bulkScopes.forEach((scope) => {
        const checkAll = scope.querySelector('[data-check-all]');
        const items = Array.from(scope.querySelectorAll('[data-bulk-item]'));
        const toolbar = scope.querySelector('[data-bulk-toolbar]');
        const countTarget = scope.querySelector('[data-bulk-count]');

        const sync = () => {
            const selected = items.filter((item) => item.checked).length;

            if (countTarget) countTarget.textContent = String(selected);
            if (toolbar) toolbar.hidden = selected === 0;

            if (checkAll) {
                checkAll.checked = items.length > 0 && selected === items.length;
                checkAll.indeterminate = selected > 0 && selected < items.length;
            }
        };

        checkAll?.addEventListener('change', () => {
            items.forEach((item) => {
                item.checked = checkAll.checked;
            });
            sync();
        });

        items.forEach((item) => item.addEventListener('change', sync));
        sync();
    });

    /* ================= Confirmation modal ================= */
    const confirmModalElement = document.getElementById('adminConfirmModal');
    const confirmMessage = document.getElementById('adminConfirmMessage');
    const confirmTitle = document.getElementById('adminConfirmTitle');
    const confirmIcon = document.getElementById('adminConfirmIcon');
    const confirmButton = document.getElementById('adminConfirmButton');
    const confirmModal = confirmModalElement && window.bootstrap
        ? bootstrap.Modal.getOrCreateInstance(confirmModalElement)
        : null;

    let pendingAction = null;

    const selectedCountForForm = (form) => form
        ? form.querySelectorAll('[data-bulk-item]:checked').length
        : 0;

    const markSubmitting = (form, submitter) => {
        const button = submitter || form?.querySelector('button[type="submit"], input[type="submit"]');
        if (!button) return;

        button.classList.add('is-loading');
        button.disabled = true;

        if (button.tagName === 'BUTTON') {
            button.dataset.originalHtml = button.innerHTML;
            const text = button.textContent.trim() || 'Memproses';
            button.innerHTML = `<span>${text}</span>`;
        }
    };

    const showConfirmation = ({ message, title = 'Konfirmasi tindakan', danger = false, action }) => {
        if (!confirmModal || !confirmMessage || !confirmButton) {
            if (window.confirm(message)) action();
            return;
        }

        pendingAction = action;
        confirmMessage.textContent = message;
        confirmTitle.textContent = title;
        confirmIcon?.classList.toggle('is-danger', danger);
        confirmButton.className = danger ? 'btn btn-danger' : 'btn btn-admin-primary';
        confirmButton.textContent = danger ? 'Ya, hapus' : 'Ya, lanjutkan';
        confirmModal.show();
    };

    confirmButton?.addEventListener('click', () => {
        const action = pendingAction;
        pendingAction = null;
        confirmModal?.hide();
        action?.();
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const submitter = event.submitter;

        if (form.dataset.confirmed === '1') {
            delete form.dataset.confirmed;
            markSubmitting(form, submitter);
            return;
        }

        const isBulkForm = form.hasAttribute('data-bulk-form') || form.hasAttribute('data-bulk-scope');
        const selected = selectedCountForForm(form);

        if (isBulkForm && selected === 0) {
            event.preventDefault();
            showConfirmation({
                title: 'Belum ada data dipilih',
                message: 'Pilih minimal satu data terlebih dahulu sebelum menjalankan aksi.',
                danger: false,
                action: () => {},
            });
            return;
        }

        const rawMessage = submitter?.dataset.confirm || form.dataset.confirm;

        if (!rawMessage) {
            markSubmitting(form, submitter);
            return;
        }

        event.preventDefault();
        const message = rawMessage.replaceAll('{count}', String(selected));
        const danger = /hapus|permanen|sampah|delete/i.test(message);

        showConfirmation({
            message,
            danger,
            action: () => {
                form.dataset.confirmed = '1';
                if (submitter) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
            },
        });
    });

    document.querySelectorAll('a[data-confirm]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const message = link.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan?';
            showConfirmation({
                message,
                danger: /hapus|permanen|sampah|delete/i.test(message),
                action: () => {
                    window.location.href = link.href;
                },
            });
        });
    });

    /* ================= Clean alerts ================= */
    document.querySelectorAll('.alert').forEach((alert) => {
        if (alert.querySelector('.admin-alert-close')) return;

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'admin-alert-close';
        close.setAttribute('aria-label', 'Tutup pesan');
        close.innerHTML = '<i class="bi bi-x-lg"></i>';
        close.addEventListener('click', () => alert.remove());
        alert.appendChild(close);

        if (alert.classList.contains('alert-success')) {
            window.setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-4px)';
                window.setTimeout(() => alert.remove(), 220);
            }, 5500);
        }
    });

    /* ================= Automatic client-side table search ================= */
    document.querySelectorAll('.admin-card').forEach((card, cardIndex) => {
        if (card.hasAttribute('data-no-auto-search')) return;

        const table = card.querySelector('.admin-table');
        const header = card.querySelector('.admin-card-header');
        if (!table || !header || header.querySelector('[data-table-search]')) return;

        const tbody = table.tBodies[0];
        if (!tbody) return;

        const rows = Array.from(tbody.rows).filter((row) => !row.querySelector('.admin-empty'));
        if (rows.length < 5) return;

        const searchWrap = document.createElement('div');
        searchWrap.className = 'admin-table-search';
        searchWrap.dataset.tableSearch = String(cardIndex);
        searchWrap.innerHTML = `
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Cari pada tabel..." aria-label="Cari data pada tabel">
        `;

        header.appendChild(searchWrap);
        const input = searchWrap.querySelector('input');
        let emptyRow = null;

        input.addEventListener('input', () => {
            const keyword = input.value.trim().toLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                const match = keyword === '' || row.textContent.toLowerCase().includes(keyword);
                row.hidden = !match;
                if (match) visible += 1;
            });

            if (visible === 0 && keyword !== '') {
                if (!emptyRow) {
                    emptyRow = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = table.tHead?.rows[0]?.cells.length || 1;
                    cell.className = 'admin-table-empty-search';
                    cell.innerHTML = '<i class="bi bi-search me-2"></i>Data yang dicari tidak ditemukan.';
                    emptyRow.appendChild(cell);
                    tbody.appendChild(emptyRow);
                }
                emptyRow.hidden = false;
            } else if (emptyRow) {
                emptyRow.hidden = true;
            }
        });
    });

    /* ================= Back to top ================= */
    const backToTop = document.getElementById('adminBackToTop');

    const syncBackToTop = () => {
        backToTop?.classList.toggle('is-visible', window.scrollY > 420);
    };

    window.addEventListener('scroll', syncBackToTop, { passive: true });
    backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    syncBackToTop();

    /* Tooltips for compact sidebar and icon buttons */
    if (window.bootstrap) {
        document.querySelectorAll('[title]').forEach((element) => {
            if (element.closest('.admin-menu') || element.classList.contains('admin-icon-button')) {
                bootstrap.Tooltip.getOrCreateInstance(element, {
                    placement: element.closest('.admin-menu') ? 'right' : 'bottom',
                    trigger: 'hover focus',
                });
            }
        });
    }
})();
