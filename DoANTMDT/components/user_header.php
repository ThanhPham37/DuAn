<?php
if (isset($message)) {
   // Nếu $message là mảng thì lặp qua từng thông báo
   if (is_array($message)) {
      foreach ($message as $msg) {
         echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
      }
   } else {
      // Nếu chỉ là chuỗi đơn, hiển thị trực tiếp
      echo '<p class="message">' . htmlspecialchars($message) . '</p>';
   }
}
?>

<header class="header">

   <section class="flex">

      <a href="home.php" class="logo">Coffee & Tea😋</a>

      <nav class="navbar">
         <a href="home.php">Trang Chủ</a>
         <a href="menu.php">Menu</a>
         <a href="orders.php">Đơn Hàng</a>
         <a href="contact.php">Liên Hệ</a>
         <a href="about.php">About</a>
      </nav>

      <div class="icons">
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <a href="search.php"><i class="fas fa-search"></i></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_items; ?>)</span></a>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="menu-btn" class="fas fa-bars"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$user_id]);
            if($select_profile->rowCount() > 0){
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p class="name"><?= $fetch_profile['name']; ?></p>
         <div class="flex">
            <a href="profile.php" class="btn">Hồ Sơ</a>
            <a href="components/user_logout.php" onclick="return confirm('Đăng Xuất Khỏi Trang Hiện Tại?');" class="delete-btn">Đăng Xuất</a>
         </div>
         <p class="account">
            <a href="login.php">Đăng Nhập</a> or
            <a href="register.php">Đăng Ký</a>
         </p> 
         <?php
            }else{
         ?>
            <p class="name">Hãy Đăng Nhập Trước!</p>
            <a href="login.php" class="btn">Đăng Nhập</a>
         <?php
          }
         ?>
      </div>

   </section>

</header>

