<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div>
                <h4 class="header-title mb-3">Welcome !</h4>
            </div>
        </div>
    </div>
    <!-- end row -->



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

    <!-- Bảng dữ liệu -->
    <div class="row mt-4">
        <div class="col-md-12">
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
                                        <td class="text-center"><a href="#"><?= $order['customer_code'] ?> (<?= $order['customer_name'] ?>)</a></td>
                                        <td class="text-center"><?= timeAgo($order['created_at']) ?></td>
                                        <td class="text-center">
                                            <span class="badge 
                                                <?php
                                                switch ($order['order_status']) {
                                                    case 'in_stock':
                                                        echo 'badge-danger';
                                                        break; // Tồn kho (chưa có invoice)
                                                    case 'shipping':
                                                        echo 'badge-warning';
                                                        break; // Đang giao (pending)
                                                    case 'shipped':
                                                        echo 'badge-success';
                                                        break; // Đã giao (confirmed)
                                                    default:
                                                        echo 'bg-danger'; // Không xác định
                                                }
                                                ?>">
                                                <?php
                                                switch ($order['order_status']) {
                                                    case 'in_stock':
                                                        echo 'Tồn kho';
                                                        break;
                                                    case 'shipping':
                                                        echo 'Đang giao';
                                                        break;
                                                    case 'shipped':
                                                        echo 'Đã giao';
                                                        break;
                                                    default:
                                                        echo 'Không xác định';
                                                }
                                                ?>
                                            </span>

                                        </td>
                                        <td class="text-center">
                                            <a href="/orders/view/<?= $order['id'] ?>" class="btn btn-sm btn-info">Chi tiết</a>
                                        </td>
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
        <div class="col-md-12">
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
                                    <th>Zalo</th>
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
                                        <td class="text-center"><?= $customer['zalo_link'] ?></td>
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