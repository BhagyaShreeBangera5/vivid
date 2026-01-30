<?php
session_start();

/* ================= DATABASE CONNECTION ================= */
$conn = new mysqli("localhost", "root", "", "vivid_graphics");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/* ===== OPTIONAL DEBUG (use only when needed) ===== */
/*
$result = $conn->query("SELECT DATABASE()");
$row = $result->fetch_row();
die("Connected DB: " . $row[0]);
*/

/* ================= QUERY ================= */
$sql = "
SELECT 
    o.id AS order_id,
    o.created_at,
    i.product,
    i.size,
    i.quantity,
    i.design
FROM `orders` o
JOIN `order_items` i ON o.id = i.order_id
ORDER BY o.id DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        table {
            width: 95%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th { background: #222; color: #fff; }
        .logout {
            margin: 20px;
            display: inline-block;
            padding: 8px 15px;
            background: red;
            color: #fff;
            text-decoration: none;
        }
        img {
            border-radius: 6px;
        }
    </style>
</head>

<body>

<h2 style="text-align:center">Admin Dashboard</h2>
<a class="logout" href="logout.php">Logout</a>

<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Product</th>
            <th>Size</th>
            <th>Quantity</th>
            <th>Design</th>
            <th>Order Date</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>VG-<?= $row['order_id'] ?></td>
            <td><?= htmlspecialchars($row['product']) ?></td>
            <td><?= htmlspecialchars($row['size']) ?></td>
            <td><?= (int)$row['quantity'] ?></td>
            <td>
                <?php if (!empty($row['design'])): ?>
                    <img src="/vivid/<?= htmlspecialchars($row['design']) ?>" width="80">
                <?php else: ?>
                    No Design
                <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>

<?php $conn->close(); ?>
