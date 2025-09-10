document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const filterForm = document.getElementById('filter-form');

    if (!filterForm) return;

    filterForm.addEventListener('submit', function() {
        const submitBtn = filterForm.querySelector('button[type="submit"]');
        const searchIcon = submitBtn ? submitBtn.querySelector('i') : null;

        if (searchIcon) {
            searchIcon.className = 'bi bi-arrow-repeat';
            searchIcon.style.animation = 'spin 1s linear infinite';
        }

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        if (!document.querySelector('#loading-style')) {
            const style = document.createElement('style');
            style.id = 'loading-style';
            style.textContent = `
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    });

    function highlightText(element, term) {
        const text = element.textContent;
        const regex = new RegExp(`(${term})`, 'gi');
        const highlightedText = text.replace(regex, '<mark>$1</mark>');
        element.innerHTML = highlightedText;
    }

    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    if (searchTerm) {
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            const nameCell = row.cells[2];
            const emailCell = row.cells[3];

            if (nameCell && nameCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(nameCell, searchTerm);
            }

            if (emailCell && emailCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(emailCell, searchTerm);
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (!searchInput) return;
        
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }

        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            filterForm.submit();
        }
    });

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const filterCollapseEl = document.getElementById('filterCollapse');
    const filterToggleIcon = document.getElementById('filterToggleIcon');
    const filterToggleBtn = document.getElementById('filterToggleBtn');

    if (filterCollapseEl && filterToggleIcon) {
        if (filterToggleBtn) {
            new bootstrap.Tooltip(filterToggleBtn);
        }

        const savedState = localStorage.getItem('usersFilterCollapsed');
        if (savedState === 'true') {
            filterCollapseEl.classList.remove('show');
            filterToggleIcon.className = 'bi bi-chevron-down';
            filterToggleBtn.setAttribute('aria-expanded', 'false');
        }

        filterCollapseEl.addEventListener('show.bs.collapse', function() {
            filterToggleIcon.className = 'bi bi-chevron-up';
            localStorage.setItem('usersFilterCollapsed', 'false');
        });

        filterCollapseEl.addEventListener('hide.bs.collapse', function() {
            filterToggleIcon.className = 'bi bi-chevron-down';
            localStorage.setItem('usersFilterCollapsed', 'true');
        });
    }
});

