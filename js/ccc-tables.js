(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initSortableTables();
    });

    function initSortableTables() {
        const lists = document.querySelectorAll('.ccc-chronicles-list, .ccc-coordinators-list');
        
        lists.forEach(function(list) {
            const header = list.querySelector('.ccc-list-header');
            if (!header) return;

            const columns = header.querySelectorAll('div');
            
            columns.forEach(function(col, index) {
                col.addEventListener('click', function() {
                    sortTable(list, index, col);
                });
            });
        });
    }

    function sortTable(list, columnIndex, clickedHeader) {
        const header = list.querySelector('.ccc-list-header');
        const rows = Array.from(list.querySelectorAll('.ccc-list-row'));
        
        if (rows.length === 0) return;

        // Determine sort direction
        const isAsc = clickedHeader.classList.contains('sort-asc');
        const direction = isAsc ? -1 : 1;

        // Clear all sort classes
        header.querySelectorAll('div').forEach(function(col) {
            col.classList.remove('sort-asc', 'sort-desc');
        });

        // Set new sort class
        clickedHeader.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

        // Sort rows
        rows.sort(function(a, b) {
            const aCell = a.children[columnIndex];
            const bCell = b.children[columnIndex];
            
            if (!aCell || !bCell) return 0;

            const aText = (aCell.textContent || '').trim().toLowerCase();
            const bText = (bCell.textContent || '').trim().toLowerCase();

            // Handle empty/dash values - push to end
            if (aText === '—' || aText === '') return 1;
            if (bText === '—' || bText === '') return -1;

            // Natural sort for mixed content
            return aText.localeCompare(bText, undefined, { numeric: true }) * direction;
        });

        // Re-append rows in sorted order
        rows.forEach(function(row) {
            list.appendChild(row);
        });
    }
})();