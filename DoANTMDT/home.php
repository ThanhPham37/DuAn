<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

include 'components/add_cart.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Trang Chủ</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="./fontawesome-free-6.2.0-web/css/all.min.css">
   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>



   <section class="hero">

      <div class="swiper hero-slider">

         <div class="swiper-wrapper">

            <div class="swiper-slide slide">
               <div class="content">
                  <span>Đặt Online</span>
                  <h3>Bạc Xỉu Đường Nâu</h3>
                  <a href="menu.php" class="btn">Xem Menu</a>
               </div>
               <div class="image">
                  <img src="images/home1.png" alt="">
               </div>
            </div>

            <div class="swiper-slide slide">
               <div class="content">
                  <span>Đặt Online</span>
                  <h3>Trà Kiwi</h3>
                  <a href="menu.php" class="btn">Xem Menu</a>
               </div>
               <div class="image">
                  <img src="images/home2.png" alt="">
               </div>
            </div>

            <div class="swiper-slide slide">
               <div class="content">
                  <span>Đặt Online</span>
                  <h3>Sinh Tố Việt Quất mixed Sữa Chua</h3>
                  <a href="menu.html" class="btn">Xem Menu</a>
               </div>
               <div class="image">
                  <img src="images/home3.png" alt="">
               </div>
            </div>

         </div>

         <div class="swiper-pagination"></div>

      </div>

   </section>

   <section class="category">

      <h1 class="title">Loại Sản Phẩm</h1>

      <div class="box-container">

         <a href="category.php?category=Smoothie" class="box">
            <img src="images/cat1.png" alt="">
            <h3>Sinh Tố</h3>
         </a>

         <a href="category.php?category=Tea" class="box">
            <img src="images/cat2.png" alt="">
            <h3>Trà</h3>
         </a>

         <a href="category.php?category=Coffee" class="box">
            <img src="images/cat3.png" alt="">
            <h3>Cà Phê</h3>
         </a>

         <a href="category.php?category=Desserts" class="box">
            <img src="images/cat-4.png" alt="">
            <h3>Tráng Miệng</h3>
         </a>

      </div>

   </section>




   <section class="products">

      <h1 class="title">Các Món Mới Nhất</h1>

      <div class="box-container">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
                  <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
                  <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
                  <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button>
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                  <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
                  <div class="name"><?= $fetch_products['name']; ?></div>
                  <div class="flex">
                     <div class="price"><?= $fetch_products['price']; ?><span>VNĐ</span></div>
                     <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                  </div>
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">Chưa Có Sản Phẩm!</p>';
         }
         ?>

      </div>

      <div class="more-btn">
         <a href="menu.php" class="btn">Xem Tất Cả</a>
      </div>


   </section>















   <di id="backtop">
      <i class="fa-solid fa-arrow-up"></i>
   </di>

   <?php include 'components/footer.php'; ?>


   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

   <script>
      var swiper = new Swiper(".hero-slider", {
         loop: true,
         grabCursor: true,
         effect: "flip",
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
      });
   </script>
   <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
   <script>
      var showGoToTop = 1000;
      $(window).scroll(function() {
         console.log($(this).scrollTop());
      });

      $(document).ready(function() {
         $(window).scroll(function() {
            if ($(this).scrollTop() >= showGoToTop) {
               $('#backtop').fadeIn();
            } else {
               $('#backtop').fadeOut();
            }

         });
         $('#backtop').click(function() {
            $('html, body').animate({
               scrollTop: 0
            }, 1000);
         })
      })
   </script>

</body>

</html>