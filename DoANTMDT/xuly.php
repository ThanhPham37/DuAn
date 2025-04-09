<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   // Lấy dữ liệu từ form
   $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
   $name = isset($_POST['name']) ? $_POST['name'] : '';
   $number = isset($_POST['number']) ? $_POST['number'] : '';
   $email = isset($_POST['email']) ? $_POST['email'] : '';
   $method = isset($_POST['method']) ? $_POST['method'] : '';  // Kiểm tra phương thức thanh toán
   $address = isset($_POST['address']) ? $_POST['address'] : '';
   $total_products = isset($_POST['total_products']) ? $_POST['total_products'] : '';
   $total_price = isset($_POST['total_price']) ? $_POST['total_price'] : '';

   // Kiểm tra nếu phương thức thanh toán là "Momo"
   session_start();

   // Kiểm tra nếu phương thức thanh toán là "Momo"
   if ($method == 'momo') {
      // Tiến hành xử lý thanh toán Momo ở đây, ví dụ gọi API Momo hoặc lưu đơn hàng vào cơ sở dữ liệu
      include('xulythanhtoanmomo.php');
   }
   if ($method == 'atm') {
      // Tiến hành xử lý thanh toán ATM ở đây, ví dụ gọi API ATM hoặc lưu đơn hàng vào cơ sở dữ liệu
      // Đảm bảo rằng đường dẫn tới file là chính xác
      include('thanhtoanatm.php');
   }
}
?>
