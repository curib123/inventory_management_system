    </main>

    <script src="https://cdn.datatables.net/v/dt/dt-3.1.1/datatables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('table[data-datatable-server]').forEach(function (table) {
                var source = table.getAttribute('data-source');
                if (!source) {
                    return;
                }

                var nonOrderable = [];
                table.querySelectorAll('thead th').forEach(function (th, index) {
                    if (th.getAttribute('data-orderable') === 'false') {
                        nonOrderable.push(index);
                    }
                });

                var options = {
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: source,
                        type: 'GET'
                    },
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    searchDelay: 300,
                    order: []
                };

                if (nonOrderable.length > 0) {
                    options.columnDefs = [{
                        targets: nonOrderable,
                        orderable: false,
                        searchable: false
                    }];
                }

                new DataTable(table, options);
            });
        });
    </script>
</body>
</html>
