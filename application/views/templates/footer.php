    </main>

    <script src="https://cdn.datatables.net/v/dt/dt-3.1.1/datatables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('main table').forEach(function (table) {
                if (table.querySelector('input:not([type="hidden"]), select, textarea')) {
                    return;
                }

                var headerCells = table.querySelectorAll('thead th').length;
                if (headerCells === 0) {
                    return;
                }

                var body = table.tBodies.length ? table.tBodies[0] : null;
                if (body && body.rows.length === 1) {
                    var onlyRow = body.rows[0];
                    if (onlyRow.cells.length === 1 && onlyRow.cells[0].hasAttribute('colspan')) {
                        body.deleteRow(0);
                    }
                }

                new DataTable(table, {
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    order: []
                });
            });
        });
    </script>
</body>
</html>
