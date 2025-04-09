<?php

include 'components/connect.php';
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51R5H7EG88ArdARudk78ebns2lvKHlGBYdJjkI34DpJPxDCT8Emahb8D1nlPCWRBot495B35mEdfLqe77DLvslT1B000HM2zG2m');

session_start();

$user_id = $_SESSION['user_id'] ?? '';
if (!$user_id) {
   header('location:home.php');
   exit();
}

if (isset($_POST['submit'])) {
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
   $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'] ?? '';
   $total_price = $_POST['total_price'] ?? 0;

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if ($check_cart->rowCount() == 0) {
      $message[] = 'Giỏ Hàng Của Bạn Trống!';
   } elseif ($address == '') {
      $message[] = 'Hãy Nhập Địa Chỉ Của Bạn!';
   } elseif ($method == "cash on delivery") {
      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

      $message[] = 'Đặt Hàng Thành Công!';
   } elseif ($method == "credit card") {
      $grand_total = 0;
      $cart_items = [];
      $line_items = [];
      $shipping_fee = 20000;

      while ($fetch_cart = $check_cart->fetch(PDO::FETCH_ASSOC)) {
         $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')';
         $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);

         $line_items[] = [
            'price_data' => [
               'currency' => 'vnd',
               'product_data' => ['name' => $fetch_cart['name']],
               'unit_amount' => $fetch_cart['price'],
            ],
            'quantity' => $fetch_cart['quantity'],
         ];
      }

      $line_items[] = [
         'price_data' => [
            'currency' => 'vnd',
            'product_data' => ['name' => 'Phí giao hàng'],
            'unit_amount' => $shipping_fee,
         ],
         'quantity' => 1,
      ];

      $grand_total += $shipping_fee;

      $_SESSION['checkout_data'] = [
         'name' => $name,
         'number' => $number,
         'email' => $email,
         'method' => $method,
         'address' => $address,
         'total_products' => implode(', ', $cart_items). ', Phí giao hàng',
         'total_price' => $grand_total,
         'shipping_fee' => $shipping_fee
      ];

      try {
         $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => 'http://localhost:3000/DoANTMDT/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:3000/DoANTMDT/cancel.php',
         ]);

         header("Location: " . $session->url);
         exit();
      } catch (Exception $e) {
         echo 'Lỗi khi tạo Stripe Session: ' . $e->getMessage();
      }
   } elseif ($method == "atm") {
      $shipping_fee = 20000;
      $grand_total = 0;
      $cart_items = [];
      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      while ($item = $select_cart->fetch(PDO::FETCH_ASSOC)) {
         $cart_items[] = $item['name'] . ' (' . $item['price'] . ' x ' . $item['quantity'] . ')';
         $grand_total += ($item['price'] * $item['quantity']);
      }
   
      $cart_items[] = 'Phí giao hàng';
      $grand_total += $shipping_fee;
   
      $_SESSION['checkout_data'] = [
         'name' => $name,
         'number' => $number,
         'email' => $email,
         'method' => $method,
         'address' => $address,
         'total_products' => implode(', ', $cart_items),
         'total_price' => $grand_total,
         'shipping_fee' => $shipping_fee
      ];
   
      header("Location: thanhtoanatm.php?amount=$total_price&total_products=" . urlencode(implode(', ', $cart_items)));
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Thanh Toán</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<!-- header section starts  -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<div class="heading">
   <h3>Thanh Toán</h3>
   <p><a href="home.php">Trang Chủ</a> <span> / Thanh Toán</span></p>
</div>

<section class="checkout">

   <h1 class="title">Tóm Tắt Đơn Hàng</h1>

<form id="checkoutForm" action="" method="post">

   <div class="cart-items">
      <h3>Sản Phẩm Trong Giỏ Hàng</h3>
      <?php
         $grand_total = 0;
         $cart_items[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
               echo "<p><span class='name1'>{$fetch_cart['name']}</span><span class='price'>{$fetch_cart['price']} VNĐ x {$fetch_cart['quantity']}</span></p>";
            }
         }else{
            echo '<p class="empty">Giỏ Hàng Của Bạn Trống!</p>';
         }
      ?>
      <p id="shipping-fee" style="display: flex; justify-content: space-between;"><span class="name1">Phí Ship (COD):</span> <span class="price">20,000 VNĐ</span></p>
      <p class="grand-total"><span class="name">Tổng Tiền :</span><span class="price" id="total-price" data-original="<?= $grand_total ?>"><?= number_format($grand_total) ?>VNĐ</span></p>
      
      <a href="cart.php" class="btn">Xem Giỏ Hàng</a>
   </div>

   <input type="hidden" name="total_products" value="<?= $total_products; ?>">
   <input type="hidden" name="total_price" id="total_price_input" value="<?= $grand_total; ?>" value="">
   <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
   <input type="hidden" name="number" value="<?= $fetch_profile['number'] ?>">
   <input type="hidden" name="email" value="<?= $fetch_profile['email'] ?>">
   <input type="hidden" name="address" value="<?= $fetch_profile['address'] ?>">

   <div class="user-info">
      <h3>Thông Tin Của Bạn</h3>
      <p><i class="fas fa-user"></i><span><?= $fetch_profile['name'] ?></span></p>
      <p><i class="fas fa-phone"></i><span><?= $fetch_profile['number'] ?></span></p>
      <p><i class="fas fa-envelope"></i><span><?= $fetch_profile['email'] ?></span></p>
      <a href="update_profile.php" class="btn">Cập Nhật Thông Tin</a>
      <h3>Địa Chỉ Giao Hàng</h3>
      <p><i class="fas fa-map-marker-alt"></i><span><?php if($fetch_profile['address'] == ''){echo 'Hãy Nhập Địa Chỉ Của Bạn';}else{echo $fetch_profile['address'];} ?></span></p>
      <a href="update_address.php" class="btn">Cập Nhật Địa Chỉ</a>
      <select name="method" class="box" required required id="paymentMethod">
         <option value="" disabled selected>Chọn Phương Thức Thanh Toán --</option>
         <option value="cash on delivery">Cash On Delivery</option>
         <option value="credit card">Credit Card</option>
         <option value="atm">Momoatm</option>
      </select>
      
      <input type="submit" value="Đặt Hàng" class="btn <?php if($fetch_profile['address'] == ''){echo 'disabled';} ?>" style="width:100%; background:var(--red); color:var(--white);" name="submit">
   </div>

</form>
   
</section>








<!-- footer section starts  -->
<?php include 'components/footer.php'; ?>
<!-- footer section ends -->

<script>
document.addEventListener('DOMContentLoaded', function () {
   const form = document.getElementById('checkoutForm');
   const paymentSelect = document.getElementById('paymentMethod');
   const totalPriceElement = document.getElementById('total-price');
   const totalPriceInput = document.getElementById('total_price_input');
   const shippingFeeElement = document.getElementById('shipping-fee');

   const originalPrice = parseInt(totalPriceElement.getAttribute('data-original'));
   const shippingFee = 20000;

   function formatCurrency(value) {
      return value.toLocaleString('vi-VN') + ' VNĐ';
   }

   paymentSelect.addEventListener('change', function () {
      if (this.value === 'cash on delivery') {
         const newTotal = originalPrice + shippingFee;
         totalPriceElement.textContent = formatCurrency(newTotal);
         totalPriceInput.value = newTotal;
         shippingFeeElement.style.display = 'block';
      } else if(this.value === 'credit card') {
         const newTotal = originalPrice + shippingFee;
         totalPriceElement.textContent = formatCurrency(newTotal);
         totalPriceInput.value = newTotal;
         shippingFeeElement.style.display = 'block';
      } else if(this.value === 'atm'){
         const newTotal = originalPrice + shippingFee;
         totalPriceElement.textContent = formatCurrency(newTotal);
         totalPriceInput.value = newTotal;
         shippingFeeElement.style.display = 'block';
      }
   });

   form.addEventListener('submit', function (e) {
      const method = paymentSelect.value;
      if (method === 'atm') {
         form.action = 'xuly.php';
      } else {
         form.action = ''; // Gửi về lại chính checkout.php
      }
   });
});
</script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>