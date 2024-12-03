<?php 
class historicModel {
    public $conn;
    public function __construct()
    { 
        $this-> conn = connect_db();  
    }
    // Lấy thông tin chi tiết đơn hàng của người dùng theo user_id
    public function getOrderHistoryByUserId($user_id) {
        $sql = "SELECT o.order_id, o.product_id, o.quantity, o.price, od.name, od.email, od.phone, od.address, od.note
                FROM orders o
                JOIN order_details od ON o.order_detail_id = od.order_detail_id
                WHERE o.user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về danh sách đơn hàng
    }
    // chi tiet don hang
    public function getOrderDetails($order_id) {
        $sql = "SELECT o.order_id, o.quantity, o.price AS order_price, 
                       p.name, p.price AS product_price
                FROM orders o
                JOIN products p ON o.product_id = p.product_id
                WHERE o.order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về danh sách chi tiết đơn hàng
    }
    public function __destruct()
    {
        $this -> conn = null;
    }

}



?>