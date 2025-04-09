<?php
include 'components/connect.php';

session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
   exit;
}

if (isset($_POST['submit'])) {

   // Lấy các giá trị từ form
   $tinh = $_POST['tinh'];
   $quan = $_POST['quan'];
   $phuong = $_POST['phuong'];
   $hem_sonha = $_POST['hem_sonha'];
   $tinh_name = $_POST['tinh_name'];  // Tên đầy đủ của tỉnh
   $quan_name = $_POST['quan_name'];  // Tên đầy đủ của quận
   $phuong_name = $_POST['phuong_name'];  // Tên đầy đủ của phường

   // Kết hợp địa chỉ
   $address = '' . $hem_sonha . ', Phường, Xã: ' . $phuong_name . ', Quận, Huyện: ' . $quan_name . ', Tỉnh, Thành: ' . $tinh_name;
   $address = filter_var($address, FILTER_SANITIZE_STRING); // Lọc địa chỉ để bảo mật

   // Cập nhật địa chỉ vào cơ sở dữ liệu
   $update_address = $conn->prepare("UPDATE `users` SET address = ? WHERE id = ?");
   $update_address->execute([$address, $user_id]);

   $message[] = 'Lưu Địa Chỉ Thành Công!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cập Nhật Địa Chỉ</title>

   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Địa Chỉ Của Bạn</h3>

      <div class="form-group">
         <label for="tinh">Chọn Tỉnh Thành</label>
         <select class="css_select" id="tinh" name="tinh" title="Chọn Tỉnh Thành">
            <option value="0">Tỉnh Thành</option>
         </select>
      </div>

      <div class="form-group">
         <label for="quan">Chọn Quận, Huyện</label>
         <select class="css_select" id="quan" name="quan" title="Chọn Quận Huyện">
            <option value="0">Quận Huyện</option>
         </select>
      </div>

      <div class="form-group">
         <label for="phuong">Chọn Phường, Xã</label>
         <select class="css_select" id="phuong" name="phuong" title="Chọn Phường Xã">
            <option value="0">Phường Xã</option>
         </select>
      </div>

      <!-- New section for 'Hẻm, số nhà' -->
      <div class="form-group">
         <label for="hem_sonha">Hẻm, Số Nhà</label>
         <input type="text" id="hem_sonha" name="hem_sonha" placeholder="Nhập hẻm, số nhà" title="Nhập địa chỉ chi tiết như hẻm, số nhà">
      </div>

      <!-- Hidden fields to store the full names -->
      <input type="hidden" name="tinh_name" id="tinh_name" />
      <input type="hidden" name="quan_name" id="quan_name" />
      <input type="hidden" name="phuong_name" id="phuong_name" />

      <input type="submit" value="Lưu Địa Chỉ" name="submit" class="btn">
   </form>

</section>

<?php include 'components/footer.php'; ?>

<!-- Custom JS file link -->
<script src="js/script.js"></script>

<!-- Add your custom JavaScript here -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   $(document).ready(function () {
      // Lấy tỉnh thành
      $.getJSON('https://esgoo.net/api-tinhthanh/1/0.htm', function (data_tinh) {
         if (data_tinh.error == 0) {
            $.each(data_tinh.data, function (key_tinh, val_tinh) {
               // Gắn dữ liệu ID và full_name vào thuộc tính data-name của option
               $("#tinh").append('<option value="' + val_tinh.id + '" data-name="' + val_tinh.full_name + '">' + val_tinh.full_name + '</option>');
            });
         }
      });

      // Khi chọn tỉnh thành
      $("#tinh").change(function () {
         var idtinh = $(this).val();
         var nameTinh = $("#tinh option:selected").data("name"); // Lấy tên đầy đủ của tỉnh thành
         $("#tinh_name").val(nameTinh);  // Lưu tên tỉnh thành vào trường ẩn
         console.log("Tỉnh Thành: " + nameTinh);  // Kiểm tra tên tỉnh thành

         // Kiểm tra nếu tỉnh thành không phải "Thành phố Hồ Chí Minh"
         if (nameTinh !== "Thành phố Hồ Chí Minh") {
            alert("Hiện tại cửa hàng chỉ giao hàng tại khu vực Thành phố Hồ Chí Minh");
            // Đặt lại giá trị tỉnh thành về "Thành phố Hồ Chí Minh"
            $("#tinh").val("0");
            $("#tinh_name").val("");  // Xóa tên tỉnh thành trong trường ẩn
         } else {
            // Nếu tỉnh thành là "Thành phố Hồ Chí Minh", tiếp tục lấy quận huyện
            $.getJSON('https://esgoo.net/api-tinhthanh/2/' + idtinh + '.htm', function (data_quan) {
               if (data_quan.error == 0) {
                  $("#quan").html('<option value="0">Quận Huyện</option>');
                  $("#phuong").html('<option value="0">Phường Xã</option>');
                  $.each(data_quan.data, function (key_quan, val_quan) {
                     $("#quan").append('<option value="' + val_quan.id + '" data-name="' + val_quan.full_name + '">' + val_quan.full_name + '</option>');
                  });
               }
            });
         }
      });

      // Khi chọn quận huyện
      $("#quan").change(function () {
         var idquan = $(this).val();
         var nameQuan = $("#quan option:selected").data("name"); // Lấy tên đầy đủ của quận huyện
         $("#quan_name").val(nameQuan);  // Lưu tên quận huyện vào trường ẩn
         console.log("Quận Huyện: " + nameQuan);  // Kiểm tra tên quận huyện

         // Lấy phường xã
         $.getJSON('https://esgoo.net/api-tinhthanh/3/' + idquan + '.htm', function (data_phuong) {
            if (data_phuong.error == 0) {
               $("#phuong").html('<option value="0">Phường Xã</option>');
               $.each(data_phuong.data, function (key_phuong, val_phuong) {
                  $("#phuong").append('<option value="' + val_phuong.id + '" data-name="' + val_phuong.full_name + '">' + val_phuong.full_name + '</option>');
               });
            }
         });
      });

      // Khi chọn phường xã
      $("#phuong").change(function () {
         var idphuong = $(this).val();
         var namePhuong = $("#phuong option:selected").data("name"); // Lấy tên đầy đủ của phường xã
         $("#phuong_name").val(namePhuong);  // Lưu tên phường xã vào trường ẩn
         console.log("Phường Xã: " + namePhuong);  // Kiểm tra tên phường xã
      });
   });
</script>


</body>
</html>
