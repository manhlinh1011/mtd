<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubCustomerIdToShippingManagers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('shipping_managers', [
            'sub_customer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'customer_id'
            ]
        ]);

        // Thêm foreign key
        $this->forge->addForeignKey('sub_customer_id', 'customers', 'id', 'CASCADE', 'SET NULL');
    }

    public function down()
    {
        // Xóa foreign key trước
        $this->forge->dropForeignKey('shipping_managers', 'shipping_managers_sub_customer_id_foreign');

        // Xóa cột
        $this->forge->dropColumn('shipping_managers', 'sub_customer_id');
    }
}
