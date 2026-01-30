<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "vivid_graphics");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CHECK REQUEST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

// FORM DATA
$product = $_POST['product'] ?? '';
$size = $_POST['size'] ?? '';
$custom_size = $_POST['custom_size'] ?? '';
$quantity = intval($_POST['quantity'] ?? 0);

// PRICE LIST
/*$prices = [
    "Visiting Card" => 2,
    "Banner" => 50,
    "T-Shirt" => 300,
    "Jersey" => 450
];

$price = $prices[$product] ?? 0;
$total_price = $price * $quantity;*/

// ================= FILE UPLOAD =================
if (!isset($_FILES['design']) || $_FILES['design']['error'] !== UPLOAD_ERR_OK) {
    die("❌ File not received");
}

$upload_dir = __DIR__ . "/uploads/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
$original_name = $_FILES['design']['name'];
$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_types)) {
    die("❌ Invalid file type");
}

$file_name = time() . "_" . uniqid() . "." . $ext;
$file_path = $upload_dir . $file_name;

if (!move_uploaded_file($_FILES['design']['tmp_name'], $file_path)) {
    die("❌ Failed to move uploaded file");
}

$sql = "INSERT INTO orders 
(product, size, custom_size, quantity, design) 
VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "sssis",
    $product,
    $size,
    $custom_size,
    $quantity,
    $file_name
);

if (!$stmt->execute()) {
    die("❌ DB Error: " . $stmt->error);
}


$stmt->close();


// SUCCESS
echo "<h2>✅ Order placed successfully!</h2>";
echo "<p>File saved as <b>$file_name</b></p>";
echo "<a href='abc.php'>Go Back</a>";
?>
