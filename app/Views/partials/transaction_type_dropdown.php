<?php

/**
 * Partial view cho dropdown chọn loại giao dịch
 * 
 * @param array $transaction_types Danh sách loại giao dịch
 * @param string $selected_value Giá trị đã chọn
 * @param string $name Tên field (mặc định: transaction_type_id)
 * @param string $id ID của field (mặc định: transaction_type_id)
 * @param string $class CSS class (mặc định: form-control)
 * @param bool $required Có bắt buộc không (mặc định: false)
 * @param string $placeholder Placeholder text (mặc định: Chọn loại giao dịch)
 * @param string $category Lọc theo category (income/expense) - để trống để hiển thị tất cả
 */
?>

<select name="<?= $name ?? 'transaction_type_id' ?>"
    id="<?= $id ?? 'transaction_type_id' ?>"
    class="<?= $class ?? 'form-control' ?>"
    <?= ($required ?? false) ? 'required' : '' ?>>

    <option value=""><?= $placeholder ?? 'Chọn loại giao dịch' ?></option>

    <?php if (isset($category) && $category): ?>
        <!-- Hiển thị theo category -->
        <?php foreach ($transaction_types as $type): ?>
            <?php if ($type['category'] === $category): ?>
                <option value="<?= $type['id'] ?>"
                    <?= ($selected_value == $type['id']) ? 'selected' : '' ?>>
                    <?= esc($type['name']) ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Hiển thị tất cả với nhóm -->
        <?php
        $current_category = '';
        foreach ($transaction_types as $type):
            if ($type['category'] !== $current_category):
                if ($current_category !== ''): ?>
                    </optgroup>
                <?php endif; ?>
                <optgroup label="<?= $type['category'] === 'income' ? 'Thu' : 'Chi' ?>">
                <?php
                $current_category = $type['category'];
            endif;
                ?>
                <option value="<?= $type['id'] ?>"
                    <?= ($selected_value == $type['id']) ? 'selected' : '' ?>>
                    <?= esc($type['name']) ?>
                </option>
            <?php endforeach; ?>
            <?php if ($current_category !== ''): ?>
                </optgroup>
            <?php endif; ?>
        <?php endif; ?>
</select>