<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra mã vận đơn</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .timeline {
            list-style: none;
            padding: 0;
            position: relative;
        }

        .timeline:before {
            content: "";
            position: absolute;
            top: 0;
            left: 30px;
            width: 2px;
            height: 100%;
            background: #007bff;
        }

        .timeline li {
            margin-bottom: 20px;
            position: relative;
            padding-left: 60px;
        }

        .timeline li:before {
            content: "";
            position: absolute;
            left: 22px;
            top: 5px;
            width: 16px;
            height: 16px;
            background: #007bff;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 5px #007bff;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <h2 class="text-center">Tra cứu trạng thái đơn hàng</h2>

        <form action="<?= base_url('tracking/check') ?>" method="GET" class="form-inline justify-content-center mt-4">
            <input type="text" name="tracking_code" class="form-control mr-2" placeholder="Nhập mã vận đơn..." required>
            <button type="submit" class="btn btn-primary">Kiểm tra</button>
        </form>

        <div class="ketqua">
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger mt-3 text-center"><?= $error ?></div>
            <?php endif; ?>

            <?php if (isset($trackingCode) && isset($statusHistory)) : ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Mã vận đơn: <strong><?= htmlspecialchars($trackingCode) ?></strong></h5>
                        <?php if (isset($customer_code) && $customer_code == 'KO-TEN'): ?>
                            <p class="mb-1">Mã khách hàng: <strong><?= htmlspecialchars($customer_code) ?></strong> - Liên hệ CSKH để nhận đơn hàng</p>
                        <?php endif; ?>
                        <p class="mt-2">
                            Cân năng: <?= $weight ?> kg,<br>
                            Kích thước: <?= $volume ?> m3,<br>
                            Phí nội địa trung quốc: ¥<?= $domestic_fee ?>
                        </p>
                    </div>
                    <div class="card-body">
                        <ul class="timeline">
                            <?php foreach ($statusHistory as $status) : ?>
                                <li>
                                    <strong><?= date('d/m/Y H:i', strtotime($status['time'])) ?>:</strong>
                                    <?= htmlspecialchars($status['status']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>