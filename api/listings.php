
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
    if (isset($_GET['sort'])) {
      if ($_GET['sort'] === 'low') $sort = "l.price ASC";
      if ($_GET['sort'] === 'high') $sort = "l.price DESC";
    }
    $res = $conn->query("SELECT l.*, u.name as seller_name, u.department as seller_dept, u.phone as seller_phone FROM listings l JOIN users u ON l.seller_id=u.id $where ORDER BY $sort");
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
  }
 
  elseif ($action === 'getOne') {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT l.*, u.name as seller_name, u.department as seller_dept, u.phone as seller_phone FROM listings l JOIN users u ON l.seller_id=u.id WHERE l.id=$id");
    if ($res->num_rows === 0) { echo json_encode(["error" => "Not found"]); exit; }
    echo json_encode($res->fetch_assoc());
  }
 
  elseif ($action === 'getByUser') {
    $user_id = intval($_GET['user_id']);
    $res = $conn->query("SELECT l.*, u.name as seller_name FROM listings l JOIN users u ON l.seller_id=u.id WHERE l.seller_id=$user_id ORDER BY l.created_at DESC");
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
  }
 
  elseif ($action === 'stats') {
    $listings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='Available'")->fetch_assoc()['c'];
    $users    = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
    $sold     = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='Sold'")->fetch_assoc()['c'];
    echo json_encode(["listings" => $listings, "users" => $users, "sold" => $sold]);
  }
}
 
elseif ($method === 'POST') {
  $data   = json_decode(file_get_contents("php://input"), true);
  $action = $data['action'] ?? '';
 
  if ($action === 'add') {
    $title     = $conn->real_escape_string($data['title']);
    $category  = $conn->real_escape_string($data['category']);
    $price     = floatval($data['price']);
    $cond      = $conn->real_escape_string($data['condition_type']);
    $dept      = $conn->real_escape_string($data['department']);
    $desc      = $conn->real_escape_string($data['description']);
    $status    = $conn->real_escape_string($data['status'] ?? 'Available');
    $seller_id = intval($data['seller_id']);
    $conn->query("INSERT INTO listings (title, category, price, condition_type, department, description, status, seller_id) VALUES ('$title','$category',$price,'$cond','$dept','$desc','$status',$seller_id)");
    echo json_encode(["success" => true, "id" => $conn->insert_id]);
  }
 
  elseif ($action === 'delete') {
    $id      = intval($data['id']);
    $user_id = intval($data['user_id']);
    $conn->query("DELETE FROM listings WHERE id=$id AND seller_id=$user_id");
    echo json_encode(["success" => true]);
  }
 
  elseif ($action === 'updateStatus') {
    $id     = intval($data['id']);
    $status = $conn->real_escape_string($data['status']);
    $conn->query("UPDATE listings SET status='$status' WHERE id=$id");
    echo json_encode(["success" => true]);
  }
}
?>