<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "vivid_graphics");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB error"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !is_array($data)) {
    echo json_encode(["success" => false, "error" => "Invalid data"]);
    exit;
}

/* CREATE ORDER */
$conn->query("INSERT INTO orders () VALUES ()");
$order_id = $conn->insert_id;

if (!is_dir("uploads")) {
    mkdir("uploads", 0777, true);
}

foreach ($data as $i => $item) {

    /* ---------- BASE64 TO FILE ---------- */
    $image = $item['image'];

    if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
        $image = substr($image, strpos($image, ',') + 1);
        $type = strtolower($type[1]);

        if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
            continue;
        }

        $image = base64_decode($image);

        $fileName = "uploads/design_" . time() . "_$i.$type";
        file_put_contents($fileName, $image);
    } else {
        $fileName = null;
    }

    /* INSERT ITEM */
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product, size, quantity, design)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "issis",
        $order_id,
        $item['product'],
        $item['size'],
        $item['quantity'],
        $fileName
    );
    $stmt->execute();
}

echo json_encode([
    "success" => true,
    "order_id" => $order_id
]);
