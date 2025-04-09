<?php
require 'vendor/autoload.php';
include 'components/connect.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

Stripe::setApiKey('sk_test_51R5H7EG88ArdARudk78ebns2lvKHlGBYdJjkI34DpJPxDCT8Emahb8D1nlPCWRBot495B35mEdfLqe77DLvslT1B000HM2zG2m');

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   exit('Không xác định được người dùng!');
}

$message = "";
$payment_method = $_GET['method'] ?? 'stripe'; // 'stripe' hoặc 'momo'

try {
   if ($payment_method === 'stripe') {
      // Xử lý Stripe
      $session_id = $_GET['session_id'] ?? '';
      if (!$session_id) {
         throw new Exception("Thiếu mã session từ Stripe");
      }

      $session = Session::retrieve($session_id);

      if ($session->payment_status === 'paid') {
         $checkout_data = $_SESSION['checkout_data'] ?? [];
         $name = $checkout_data['name'] ?? '';
         $number = $checkout_data['number'] ?? '';
         $email = $checkout_data['email'] ?? '';
         $method = $checkout_data['method'] ?? 'Credit card';
         $address = $checkout_data['address'] ?? '';
         $total_products = $checkout_data['total_products'] ?? '';
         $total_price = $checkout_data['total_price'] ?? 0;

         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);
         // Xoá giỏ hàng nếu thanh toán thành công
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         unset($_SESSION['cart']);
         unset($_SESSION['checkout_data']);

         // Cập nhật đơn hàng nếu muốn (tuỳ chỉnh)
         $message = "Thanh toán bằng thẻ thành công! Cảm ơn bạn đã mua hàng.";
      } else {
         $message = "Thanh toán chưa hoàn tất.";
      }
   } elseif ($payment_method === 'momo') {
      // Xử lý Momo (giả sử đã thanh toán và redirect về)
      // Bạn có thể thêm verify từ IPN hoặc signature nếu cần
      $checkout_data = $_SESSION['checkout_data'] ?? [];
      $name = $checkout_data['name'] ?? '';
      $number = $checkout_data['number'] ?? '';
      $email = $checkout_data['email'] ?? '';
      $method = $checkout_data['method'] ?? 'Momoatm';
      $address = $checkout_data['address'] ?? '';
      $total_products = $checkout_data['total_products'] ?? '';
      $total_price = $checkout_data['total_price'] ?? 0;

     
      $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);
      unset($_SESSION['cart'], $_SESSION['checkout_data']);

      $message = "Thanh toán qua Momo thành công! Cảm ơn bạn đã mua hàng.";
   } else {
      $message = "Phương thức thanh toán không hợp lệ.";
   }
} catch (Exception $e) {
   $message = "Đã xảy ra lỗi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <title>Thanh Toán Thành Công</title>
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3>Thông Báo</h3>
   <p><a href="home.php">Trang Chủ</a> <span> / Thanh Toán Thành Công</span></p>
</div>

<section class="success-msg">
   <h1 class="title"><?php echo htmlspecialchars($message); ?></h1>
   <a href="cart.php" class="btn">Tiếp Tục Mua Sắm</a>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
