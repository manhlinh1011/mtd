<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div>
                <h4 class="header-title mb-3">Tổng quan</h4>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> Lưu ý: Đang có <span class="badge bg-danger"><?= $totalOverdueInvoices ?></span> phiếu xuất quá hạn 7 ngày.
                <a href="<?= base_url('invoices/overdue') ?>" class="btn btn-danger btn-sm float-end"> Xem chi tiết</a>
            </div>
        </div>
    </div>
    <!-- Thông tin thống kê -->
    <div class="row">
        <div class="col-md-3">
            <div class="card  bg-primary">
                <div class="card-body">
                    <h5 class="card-title text-white">Tổng khách hàng</h5>
                    <p class="card-text h1 text-white"><?= $totalCustomers ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title text-white">Tổng đơn hàng</h5>
                    <p class="card-text h1 text-white"><?= $totalOrders ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title text-white">Khách hàng mới (tháng)</h5>
                    <p class="card-text h1 text-white"><?= $newCustomers ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title text-white">Đơn hàng mới (tháng)</h5>
                    <p class="card-text h1 text-white"><?= $newOrders ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-3">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">Thống kê đơn hàng hôm nay</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Nhập kho trung quốc:</span>
                        <span class="badge bg-primary"><?= $todayStats['china_import'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Nhập kho việt nam:</span>
                        <span class="badge bg-success"><?= $todayStats['vietnam_import'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Đơn hàng tồn kho:</span>
                        <span class="badge bg-danger text-white"><?= $todayStats['in_stock'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Đơn hàng xuất:</span>
                        <span class="badge bg-info"><?= $todayStats['exported'] ?></span>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" href="<?= base_url('orders') ?>">Chi tiết</a>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">Thống kê phiếu xuất hôm nay</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Phiếu xuất tạo mới:</span>
                        <span class="badge bg-primary"><?= $invoiceStats['new'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Phiếu xuất được giao:</span>
                        <span class="badge bg-success"><?= $invoiceStats['shipped'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Phiếu xuất quá hạn 7 ngày:</span>
                        <span class="badge bg-danger"><?= $invoiceStats['overdue'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Đơn hàng xuất:</span>
                        <span class="badge bg-info"><?= $invoiceStats['total_orders'] ?></span>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" href="<?= base_url('invoices') ?>">Chi tiết</a>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">Thống kê giao dịch</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Số lần nạp tiền:</span>
                        <span class="badge bg-primary"><?= $transactionStats['deposit_count'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Số lần thanh toán phiếu xuất:</span>
                        <span class="badge bg-success"><?= $transactionStats['payment_count'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Số tiền nạp:</span>
                        <span class="badge bg-info"><?= number_format($transactionStats['deposit_amount'], 0, ',', '.') ?> đ</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Số tiền thanh toán phiếu xuất:</span>
                        <span class="badge bg-warning text-dark"><?= number_format($transactionStats['payment_amount'], 0, ',', '.') ?> đ</span>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" href="<?= base_url('transactions') ?>">Chi tiết</a>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">Thống kê số bao</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Tổng số bao:</span>
                        <span class="badge bg-primary"><?= $packageStats['total_packages'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Tổng số lô:</span>
                        <span class="badge bg-success"><?= $packageStats['total_batches'] ?></span>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" href="<?= base_url('orders') ?>">Chi tiết</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng dữ liệu -->
    <div class="row mt-2">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Đơn hàng gần đây</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Mã vận chuyển</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày tạo</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <?php
                                    if (!function_exists('timeAgo')) {
                                        function timeAgo($datetime)
                                        {
                                            $now = new DateTime();
                                            $createdTime = new DateTime($datetime);
                                            $diff = $now->getTimestamp() - $createdTime->getTimestamp();

                                            if ($diff < 60) {
                                                return $diff . ' giây trước';
                                            } elseif ($diff < 3600) {
                                                return floor($diff / 60) . ' phút trước';
                                            } elseif ($diff < 86400) {
                                                return floor($diff / 3600) . ' giờ trước';
                                            } elseif ($diff < 2592000) {
                                                return floor($diff / 86400) . ' ngày trước';
                                            } elseif ($diff < 31536000) {
                                                return floor($diff / 2592000) . ' tháng trước';
                                            } else {
                                                return floor($diff / 31536000) . ' năm trước';
                                            }
                                        }
                                    }
                                    ?>

                                    <tr>
                                        <td class="text-center"><?= $order['id'] ?></td>
                                        <td class="text-center"><?= $order['tracking_code'] ?></td>
                                        <td class="text-center"><a href="<?= base_url('/customers/detail/') ?>"><?= $order['customer_code'] ?> (<?= $order['customer_name'] ?>)</a></td>
                                        <td class="text-center"><?= timeAgo($order['created_at']) ?></td>
                                        <td class="text-center">
                                            <span class="badge 
                                                <?php
                                                if ($order['vietnam_stock_date'] === null):
                                                    echo 'bg-primary';
                                                elseif ($order['invoice_id'] === null):
                                                    echo 'bg-danger';
                                                elseif ($order['shipping_confirmed_at'] !== null):
                                                    echo 'bg-success';
                                                else:
                                                    echo 'bg-warning';
                                                endif;
                                                ?>">
                                                <?php
                                                if ($order['vietnam_stock_date'] === null):
                                                    echo 'Kho TQ';
                                                elseif ($order['invoice_id'] === null):
                                                    echo 'Tồn kho';
                                                elseif ($order['shipping_confirmed_at'] !== null):
                                                    echo 'Đã giao';
                                                else:
                                                    echo 'Chờ giao';
                                                endif;
                                                ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="/orders/edit/<?= $order['id'] ?>" class="btn btn-sm btn-info">Chi tiết</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Top 10 khách hàng đặt hàng nhiều nhất</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Mã khách hàng</th>
                                    <th>Số điện thoại</th>
                                    <th>Email</th>
                                    <th>Tổng số đơn hàng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; ?>
                                <?php foreach ($topCustomers as $customer): ?>
                                    <?php
                                    // Xác định màu dựa trên cấp độ với màu nhạt hơn
                                    $style = '';
                                    if ($rank == 1) {
                                        $style = 'background-color: #ffeeee;'; // Đỏ rất nhạt
                                    } elseif ($rank <= 3) {
                                        $style = 'background-color: #fff5cc;'; // Vàng rất nhạt
                                    } elseif ($rank <= 5) {
                                        $style = 'background-color: #e6f2ff;'; // Xanh dương rất nhạt
                                    } else {
                                        $style = 'background-color: #f9f9f9;'; // Xám rất nhạt
                                    }
                                    ?>
                                    <tr style="<?= $style ?>">
                                        <td class="text-center"><?= $rank++  ?></td>
                                        <td class="text-center"><?= $customer['customer_code'] ?></td>
                                        <td class="text-center"><?= $customer['phone'] ?></td>
                                        <td class="text-center"><?= $customer['email'] ?></td>
                                        <td class="text-center"><?= $customer['total_orders'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Số lượng đơn đặt hàng trong 30 ngày</h5>
                    <canvas id="ordersChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Thống kê loại hàng được đặt</h5>
                    <canvas id="productTypeChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tổng cân nặng trong 30 ngày</h5>
                    <canvas id="weightChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tổng tiền phí giao hàng trong 30 ngày</h5>
                    <canvas id="shippingFeeChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Biểu đồ đường: Số lượng đơn đặt hàng trong 30 ngày
        var ctxOrders = document.getElementById('ordersChart').getContext('2d');
        var ordersChart = new Chart(ctxOrders, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Số đơn đặt hàng',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Ngày'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Số đơn đặt hàng'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Biểu đồ cột: Thống kê loại hàng được đặt
        var ctxProductType = document.getElementById('productTypeChart').getContext('2d');
        var productTypeChart = new Chart(ctxProductType, {
            type: 'bar',
            data: {
                labels: <?= json_encode($productTypeLabels) ?>,
                datasets: [{
                    label: 'Số lượng đơn đặt hàng',
                    data: <?= json_encode($productTypeValues) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Loại hàng'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Số lượng đơn đặt hàng'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('weightChart').getContext('2d');
            var weightChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= $weightLabels ?>,
                    datasets: [{
                        label: 'Tổng cân nặng (kg)',
                        data: <?= $weightValues ?>,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Ngày'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Tổng cân nặng (kg)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx2 = document.getElementById('shippingFeeChart').getContext('2d');
            var shippingFeeChart = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: <?= $shippingFeeLabels ?>,
                    datasets: [{
                        label: 'Phí giao hàng (VND)',
                        data: <?= $shippingFeeValues ?>,
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Ngày'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Tổng phí giao hàng (VND)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</div>
<?= $this->endSection() ?>