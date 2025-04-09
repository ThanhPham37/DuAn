<?php


if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      header('location:login.php');
   }else{

      $pid = $_POST['pid'];
      $pid = filter_var($pid, FILTER_SANITIZE_STRING);
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $price = $_POST['price'];
      $price = filter_var($price, FILTER_SANITIZE_STRING);
      $image = $_POST['image'];
      $image = filter_var($image, FILTER_SANITIZE_STRING);
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      if (!isset($_SESSION['cart'])) {
         $_SESSION['cart'] = [];
     }

      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart_numbers->execute([$name, $user_id]);

      if($check_cart_numbers->rowCount() > 0){
         // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng
        $update_cart = $conn->prepare("UPDATE `cart` SET quantity = quantity + ? WHERE user_id = ? AND pid = ?");
        $update_cart->execute([$qty, $user_id, $pid]);
         $message[] = 'Cập Nhật Giỏ Hàng Thành Công!';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'Thêm Vào Giỏ Hàng Thành Công!';
         
      }

   }

}

?>