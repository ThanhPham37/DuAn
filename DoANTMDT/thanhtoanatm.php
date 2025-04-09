<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-type: text/html; charset=utf-8');

// Kết nối với cơ sở dữ liệu
include 'components/connect.php';  // Đảm bảo bạn có file connect.php kết nối cơ sở dữ liệu

// Hàm thực hiện yêu cầu POST với cURL
function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    // Thực hiện yêu cầu POST
    $result = curl_exec($ch);
    // Đóng kết nối
    curl_close($ch);
    return $result;
}

// Dữ liệu MoMo
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
$orderInfo = "Thanh toán qua ATM";
$total_price = $_POST['total_price']; 
$amount = $total_price;
$orderId = time() . "";
$redirectUrl = "http://localhost:3000/DoAnTMDT/success.php?method=momo";
$ipnUrl = "http://localhost:3000/DoAnTMDT/checkout.php";
$extraData = "";

// Tạo chuỗi hash để xác thực yêu cầu
$requestId = time() . "";
$requestType = "payWithATM";
$extraData = isset($_POST["extraData"]) ? $_POST["extraData"] : "";
$rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
$signature = hash_hmac("sha256", $rawHash, key: $secretKey);
$data = array('partnerCode' => $partnerCode,
    'partnerName' => "Test",
    "storeId" => "MomoTestStore",
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature);

// Gửi yêu cầu đến MoMo
$result = execPostRequest($endpoint, json_encode($data));
$jsonResult = json_decode($result, true);  // Giải mã phản hồi JSON

// Kiểm tra xem phản hồi có chứa payUrl không
if (isset($jsonResult['payUrl'])) {
    // Lấy thông tin người dùng từ session
    session_start();
    $total_price = $_POST['total_price'] ?? 0;
    $total_products = $_POST['total_products'] ?? '';
    $name = $_POST['name'] ?? '';
    $number = $_POST['number'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $method = 'momoatm';
    
    // Lưu thông tin order vào session để xử lý sau khi thanh toán thành công
    $_SESSION['checkout_data'] = [
        'name' => $name,
        'number' => $number,
        'email' => $email,
        'method' => $method,
        'address' => $address,
        'total_products' => $total_products,
        'total_price' => $total_price
    ];

    // Sau khi xóa giỏ hàng, chuyển hướng người dùng tới payUrl
    header('Location: ' . $jsonResult['payUrl']);
    exit();  // Dừng script sau khi chuyển hướng
} else {
    echo "Error: payUrl not found in response.";
}
?>

<form id="momoRedirect" method="POST" action="xuly.php">
   <input type="hidden" name="user_id" value="<?= $user_id ?>">
   <input type="hidden" name="amount" value="<?= $amount ?>">
   <input type="hidden" name="total_price" value="<?= $amount ?>">
   <input type="hidden" name="total_products" value="<?= $total_products ?>">
   <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
   <input type="hidden" name="number" value="<?= $fetch_profile['number'] ?>">
   <input type="hidden" name="email" value="<?= $fetch_profile['email'] ?>">
   <input type="hidden" name="address" value="<?= $fetch_profile['address'] ?>">
   <input type="hidden" name="method" value="atm">
</form>
