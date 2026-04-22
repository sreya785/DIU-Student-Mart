<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'getAll';

    if ($action === 'getAll') {
        $where = "WHERE 1=1";

        if (!empty($_GET['search'])) {
            $s = $conn->real_escape_string($_GET['search']);
            $where .= " AND (l.title LIKE '%$s%' OR l.description LIKE '%$s%')";
        }
        if (!empty($_GET['cat'])) {
            $c = $conn->real_escape_string($_GET['cat']);
            $where .= " AND l.category='$c'";
        }
        if (!empty($_GET['cond'])) {
            $co = $conn->real_escape_string($_GET['cond']);
            $where .= " AND l.condition_type='$co'";
        }

        $sort = "l.created_at DESC";
        if (!empty($_GET['sort'])) {
            if ($_GET['sort'] === 'low')  $sort = "l.price ASC";
            if ($_GET['sort'] === 'high') $sort = "l.price DESC";
        }

        $sql = "SELECT l.*, u.name as seller_name, u.department as seller_dept, u.phone as seller_phone 
                FROM listings l 
                LEFT JOIN users u ON l.seller_id = u.id 
                $where 
                ORDER BY $sort";

        $res  = $conn->query($sql);
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        echo json_encode($rows);
    }

    elseif ($action === 'getOne') {
        $id  = intval($_GET['id']);
        $sql = "SELECT l.*, u.name as seller_name, u.department as seller_dept, u.phone as seller_phone 
                FROM listings l 
                LEFT JOIN users u ON l.seller_id = u.id 
                WHERE l.id = $id";
        $res = $conn->query($sql);
        if (!$res || $res->num_rows === 0) {
            echo json_encode(["error" => "Not found"]);
            exit;
        }
        echo json_encode($res->fetch_assoc());
    }

    elseif ($action === 'getByUser') {
        $user_id = intval($_GET['user_id']);
        $sql     = "SELECT l.*, u.name as seller_name 
                    FROM listings l 
                    LEFT JOIN users u ON l.seller_id = u.id 
                    WHERE l.seller_id = $user_id 
                    ORDER BY l.created_at DESC";
        $res  = $conn->query($sql);
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        echo json_encode($rows);
    }

    elseif ($action === 'stats') {
        $listings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='Available'")->fetch_assoc()['c'];
        $users    = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
        $sold     = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='Sold'")->fetch_assoc()['c'];
        echo json_encode([
            "listings" => $listings,
            "users"    => $users,
            "sold"     => $sold
        ]);
    }
}

elseif ($method === 'POST') {
    $raw    = file_get_contents("php://input");
    $data   = json_decode($raw, true);

    if (!$data) {
        echo json_encode(["success" => false, "message" => "Invalid data received"]);
        exit;
    }

    $action = $data['action'] ?? '';

    if ($action === 'add') {
        $title     = $conn->real_escape_string(trim($data['title'] ?? ''));
        $category  = $conn->real_escape_string($data['category'] ?? '');
        $price     = floatval($data['price'] ?? 0);
        $cond      = $conn->real_escape_string($data['condition_type'] ?? 'Used');
        $dept      = $conn->real_escape_string($data['department'] ?? '');
        $desc      = $conn->real_escape_string(trim($data['description'] ?? ''));
        $status    = $conn->real_escape_string($data['status'] ?? 'Available');
        $seller_id = intval($data['seller_id'] ?? 0);

        if (!$title || !$price || !$desc || !$seller_id) {
            echo json_encode(["success" => false, "message" => "Please fill all required fields"]);
            exit;
        }

        $sql = "INSERT INTO listings (title, category, price, condition_type, department, description, status, seller_id) 
                VALUES ('$title', '$category', $price, '$cond', '$dept', '$desc', '$status', $seller_id)";

        if ($conn->query($sql)) {
            echo json_encode(["success" => true, "id" => $conn->insert_id]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
        }
    }

    elseif ($action === 'delete') {
        $id      = intval($data['id'] ?? 0);
        $user_id = intval($data['user_id'] ?? 0);
        $conn->query("DELETE FROM listings WHERE id=$id AND seller_id=$user_id");
        echo json_encode(["success" => true]);
    }

    elseif ($action === 'updateStatus') {
        $id     = intval($data['id'] ?? 0);
        $status = $conn->real_escape_string($data['status'] ?? '');
        $conn->query("UPDATE listings SET status='$status' WHERE id=$id");
        echo json_encode(["success" => true]);
    }
}
?>