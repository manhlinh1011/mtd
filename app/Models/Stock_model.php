<?php
class Stock_model extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function update_stock_date($id)
    {
        $this->db->set('vietnam_stock_date', 'CURRENT_TIMESTAMP', FALSE);
        $this->db->where('id', $id);
        $this->db->update('your_table_name');  // Thay 'your_table_name' bằng tên bảng của bạn

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
