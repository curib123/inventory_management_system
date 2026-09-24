document.addEventListener('DOMContentLoaded', function () {
    var dataElement = document.getElementById('dashboard-chart-data');

    if (!dataElement) {
        return;
    }

    var dashboardData;

    try {
        dashboardData = JSON.parse(dataElement.textContent || '{}');
    } catch (error) {
        return;
    }

    function showChartEmpty(canvas, message) {
        if (!canvas) {
            return;
        }

        canvas.classList.add('d-none');

        var shell = canvas.closest('[data-chart-shell]');
        var empty = shell ? shell.querySelector('[data-chart-empty]') : null;

        if (empty) {
            empty.classList.remove('d-none');

            if (message) {
                var detail = empty.querySelector('.small');

                if (detail) {
                    detail.textContent = message;
                }
            }
        }
    }

    if (typeof Chart === 'undefined') {
        document.querySelectorAll('.app-chart canvas').forEach(function (canvas) {
            showChartEmpty(
                canvas,
                'Charts could not load. Check the internet/CDN connection and refresh the dashboard.'
            );
        });
        return;
    }

    var rootStyles = getComputedStyle(document.documentElement);
    var bodyStyles = getComputedStyle(document.body);

    function cssValue(name, fallback) {
        var value = rootStyles.getPropertyValue(name).trim();
        return value || fallback;
    }

    var colors = {
        primary: cssValue('--dashboard-primary', '#2563eb'),
        primarySoft: cssValue('--dashboard-primary-soft', 'rgba(37, 99, 235, 0.14)'),
        success: cssValue('--dashboard-success', '#16a34a'),
        successSoft: cssValue('--dashboard-success-soft', 'rgba(22, 163, 74, 0.13)'),
        warning: cssValue('--dashboard-warning', '#d97706'),
        danger: cssValue('--dashboard-danger', '#dc2626'),
        muted: cssValue('--dashboard-muted', '#64748b'),
        grid: cssValue('--dashboard-grid', '#e2e8f0'),
        body: cssValue('--dashboard-text', bodyStyles.color || '#0f172a')
    };

    var numberFormatter = new Intl.NumberFormat();

    Chart.defaults.font.family = bodyStyles.fontFamily;
    Chart.defaults.color = colors.muted;
    Chart.defaults.borderColor = colors.grid;
    Chart.defaults.animation.duration = 450;

    var sharedTooltip = {
        backgroundColor: '#0f172a',
        titleColor: '#ffffff',
        bodyColor: '#e2e8f0',
        padding: 12,
        cornerRadius: 9,
        displayColors: true
    };

    var centerTextPlugin = {
        id: 'dashboardCenterText',
        afterDraw: function (chart, args, options) {
            if (!options || !options.display) {
                return;
            }

            var meta = chart.getDatasetMeta(0);

            if (!meta || !meta.data || !meta.data.length) {
                return;
            }

            var point = meta.data[0];
            var ctx = chart.ctx;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = colors.body;
            ctx.font = '700 22px ' + bodyStyles.fontFamily;
            ctx.fillText(numberFormatter.format(options.value || 0), point.x, point.y - 5);

            ctx.fillStyle = colors.muted;
            ctx.font = '500 11px ' + bodyStyles.fontFamily;
            ctx.fillText(options.label || 'Active', point.x, point.y + 17);
            ctx.restore();
        }
    };

    var healthCanvas = document.getElementById('stock-health-chart');
    var health = dashboardData.stockHealth || {};
    var healthValues = [
        Number(health.healthy || 0),
        Number(health.lowStock || 0),
        Number(health.outOfStock || 0)
    ];
    var healthTotal = healthValues.reduce(function (sum, value) {
        return sum + value;
    }, 0);

    if (healthCanvas) {
        if (healthTotal <= 0) {
            showChartEmpty(healthCanvas);
        } else {
            new Chart(healthCanvas, {
                type: 'doughnut',
                plugins: [centerTextPlugin],
                data: {
                    labels: ['Healthy', 'Low Stock', 'Out of Stock'],
                    datasets: [{
                        data: healthValues,
                        backgroundColor: [
                            colors.success,
                            colors.warning,
                            colors.danger
                        ],
                        borderColor: [
                            colors.success,
                            colors.warning,
                            colors.danger
                        ],
                        borderWidth: 1,
                        hoverOffset: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: Object.assign({}, sharedTooltip, {
                            callbacks: {
                                label: function (context) {
                                    var value = Number(context.raw || 0);
                                    var percent = healthTotal > 0
                                        ? Math.round((value / healthTotal) * 100)
                                        : 0;

                                    return ' ' + context.label + ': ' +
                                        numberFormatter.format(value) +
                                        ' (' + percent + '%)';
                                }
                            }
                        }),
                        dashboardCenterText: {
                            display: true,
                            value: healthTotal,
                            label: 'Active products'
                        }
                    }
                }
            });
        }
    }

    var categoryCanvas = document.getElementById('stock-category-chart');
    var categoryData = Array.isArray(dashboardData.stockByCategory)
        ? dashboardData.stockByCategory.slice()
        : [];

    categoryData.sort(function (a, b) {
        return Number(b.stock || 0) - Number(a.stock || 0);
    });

    var categoryStockTotal = categoryData.reduce(function (sum, row) {
        return sum + Number(row.stock || 0);
    }, 0);

    if (categoryCanvas) {
        if (!categoryData.length || categoryStockTotal <= 0) {
            showChartEmpty(categoryCanvas);
        } else {
            new Chart(categoryCanvas, {
                type: 'bar',
                data: {
                    labels: categoryData.map(function (row) {
                        return row.category;
                    }),
                    datasets: [{
                        label: 'Stock Quantity',
                        data: categoryData.map(function (row) {
                            return Number(row.stock || 0);
                        }),
                        backgroundColor: colors.primary,
                        borderColor: colors.primary,
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 26
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'nearest',
                        intersect: false
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: colors.grid
                            },
                            ticks: {
                                precision: 0,
                                callback: function (value) {
                                    return numberFormatter.format(value);
                                }
                            },
                            title: {
                                display: true,
                                text: 'Stock quantity'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                autoSkip: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: Object.assign({}, sharedTooltip, {
                            callbacks: {
                                label: function (context) {
                                    return ' Stock: ' +
                                        numberFormatter.format(Number(context.raw || 0));
                                },
                                afterLabel: function (context) {
                                    var row = categoryData[context.dataIndex];
                                    return ' Products: ' +
                                        numberFormatter.format(Number(row.products || 0));
                                }
                            }
                        })
                    }
                }
            });
        }
    }

    var movementCanvas = document.getElementById('stock-movement-chart');
    var movementData = Array.isArray(dashboardData.monthlyMovement)
        ? dashboardData.monthlyMovement
        : [];

    var movementTotal = movementData.reduce(function (sum, row) {
        return sum +
            Number(row.stock_in || 0) +
            Number(row.stock_out || 0);
    }, 0);

    if (movementCanvas) {
        if (!movementData.length || movementTotal <= 0) {
            showChartEmpty(movementCanvas);
        } else {
            new Chart(movementCanvas, {
                type: 'line',
                data: {
                    labels: movementData.map(function (row) {
                        return row.label;
                    }),
                    datasets: [
                        {
                            label: 'Stock In',
                            data: movementData.map(function (row) {
                                return Number(row.stock_in || 0);
                            }),
                            borderColor: colors.success,
                            backgroundColor: colors.successSoft,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            tension: 0.32,
                            fill: false
                        },
                        {
                            label: 'Stock Out',
                            data: movementData.map(function (row) {
                                return Number(row.stock_out || 0);
                            }),
                            borderColor: colors.primary,
                            backgroundColor: colors.primarySoft,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            tension: 0.32,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: colors.grid
                            },
                            ticks: {
                                precision: 0,
                                callback: function (value) {
                                    return numberFormatter.format(value);
                                }
                            },
                            title: {
                                display: true,
                                text: 'Quantity'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 8,
                                boxHeight: 8,
                                padding: 18
                            }
                        },
                        tooltip: Object.assign({}, sharedTooltip, {
                            callbacks: {
                                label: function (context) {
                                    return ' ' + context.dataset.label + ': ' +
                                        numberFormatter.format(Number(context.raw || 0));
                                }
                            }
                        })
                    }
                }
            });
        }
    }
});
