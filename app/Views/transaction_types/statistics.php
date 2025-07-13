<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Thống kê theo loại giao dịch
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thống kê theo loại giao dịch</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('transaction-types') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Bộ lọc -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="date_from">Từ ngày</label>
                            <input type="date" class="form-control" id="date_from" name="date_from">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to">Đến ngày</label>
                            <input type="date" class="form-control" id="date_to" name="date_to">
                        </div>
                        <div class="col-md-3">
                            <label for="category_filter">Phân loại</label>
                            <select class="form-control" id="category_filter">
                                <option value="">Tất cả</option>
                                <option value="income">Thu</option>
                                <option value="expense">Chi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" onclick="loadStatistics()">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>

                    <!-- Tổng quan -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="total_income">0</h3>
                                    <p>Tổng thu</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 id="total_expense">0</h3>
                                    <p>Tổng chi</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="net_amount">0</h3>
                                    <p>Lợi nhuận</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 id="total_transactions">0</h3>
                                    <p>Tổng giao dịch</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bảng thống kê chi tiết -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Thống kê chi tiết theo loại giao dịch</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="statistics_table">
                                            <thead>
                                                <tr>
                                                    <th>Loại giao dịch</th>
                                                    <th>Phân loại</th>
                                                    <th>Số lượng giao dịch</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Trung bình</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="6" class="text-center">Vui lòng chọn khoảng thời gian để xem thống kê</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biểu đồ -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Biểu đồ thu chi</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="income_expense_chart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Phân bố theo loại giao dịch</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="transaction_types_chart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let incomeExpenseChart, transactionTypesChart;

    $(document).ready(function() {
        // Set default date range (current month)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

        $('#date_from').val(firstDay.toISOString().split('T')[0]);
        $('#date_to').val(today.toISOString().split('T')[0]);

        // Load initial statistics
        loadStatistics();
    });

    function loadStatistics() {
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();
        const category = $('#category_filter').val();

        if (!dateFrom || !dateTo) {
            alert('Vui lòng chọn khoảng thời gian');
            return;
        }

        // Show loading
        $('#statistics_table tbody').html('<tr><td colspan="6" class="text-center">Đang tải...</td></tr>');

        // Load statistics via AJAX
        $.ajax({
            url: '<?= base_url('transaction-types/statistics') ?>',
            method: 'GET',
            data: {
                date_from: dateFrom,
                date_to: dateTo,
                category: category
            },
            success: function(response) {
                if (response.success) {
                    updateStatistics(response.data);
                    updateCharts(response.data);
                } else {
                    alert('Có lỗi xảy ra: ' + response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi tải dữ liệu');
            }
        });
    }

    function updateStatistics(data) {
        // Update summary boxes
        $('#total_income').text(formatCurrency(data.summary.total_income));
        $('#total_expense').text(formatCurrency(data.summary.total_expense));
        $('#net_amount').text(formatCurrency(data.summary.net_amount));
        $('#total_transactions').text(data.summary.total_transactions);

        // Update table
        let tableHtml = '';
        if (data.details.length === 0) {
            tableHtml = '<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>';
        } else {
            data.details.forEach(function(item) {
                const percentage = data.summary.total_amount > 0 ?
                    ((item.total_amount / data.summary.total_amount) * 100).toFixed(1) : 0;

                tableHtml += `
                <tr>
                    <td>${item.name}</td>
                    <td><span class="badge badge-${item.category === 'income' ? 'success' : 'danger'}">${item.category === 'income' ? 'Thu' : 'Chi'}</span></td>
                    <td>${item.transaction_count}</td>
                    <td>${formatCurrency(item.total_amount)}</td>
                    <td>${formatCurrency(item.average_amount)}</td>
                    <td>${percentage}%</td>
                </tr>
            `;
            });
        }
        $('#statistics_table tbody').html(tableHtml);
    }

    function updateCharts(data) {
        // Update income/expense chart
        if (incomeExpenseChart) {
            incomeExpenseChart.destroy();
        }

        const ctx1 = document.getElementById('income_expense_chart').getContext('2d');
        incomeExpenseChart = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Thu', 'Chi'],
                datasets: [{
                    data: [data.summary.total_income, data.summary.total_expense],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Update transaction types chart
        if (transactionTypesChart) {
            transactionTypesChart.destroy();
        }

        const ctx2 = document.getElementById('transaction_types_chart').getContext('2d');
        const labels = data.details.map(item => item.name);
        const values = data.details.map(item => item.total_amount);
        const colors = data.details.map(item => item.category === 'income' ? '#28a745' : '#dc3545');

        transactionTypesChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tổng tiền',
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return formatCurrency(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }
</script>
<?= $this->endSection() ?>