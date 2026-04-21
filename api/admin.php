<?php
require_once 'config.php';
 
$method = $_SERVER['REQUEST_METHOD'];
 
if ($method === 'GET') {
  $action = $_GET['action'] ?? '';
 
  if ($action === 'stats') {
    $total_listings   = $conn->query("SELECT COUNT(*) as c FROM listings")->fetch_assoc()['c'];
    $available        = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='Available'")->fetch_assoc()['c'];
    $total_users      = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
    $total_inquiries  = $conn->query("SELECT COUNT(*) as c FROM inquiries")->fetch_assoc()['c'];
    echo json_encode(["total_listings" => $total_listings, "available" => $available, "total_users" => $total_users, "total_inquiries" => $total_inquiries]);
  }
 
  elseif ($action === 'listings') {
    $res = $conn->query("SELECT l.*, u.name as seller_name FROM listings l JOIN users u ON l.seller_id=u.id ORDER BY l.created_at DESC");
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
  }
 
  elseif ($action === 'users') {
    $res = $conn->query("SELECT id, name, student_id, email, department, phone, role, created_at FROM users ORDER BY created_at DESC");
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
  }
 
  elseif ($action === 'inquiries') {
    $res = $conn->query("SELECT i.*, l.title as listing_title, u.name as buyer_name FROM inquiries i JOIN listings l ON i.listing_id=l.id JOIN users u ON i.buyer_id=u.id ORDER BY i.created_at DESC");
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
  }
}
 
elseif ($method === 'POST') {
  $data   = json_decode(file_get_contents("php://input"), true);
  $action = $data['action'] ?? '';
 
  if ($action === 'deleteListing') {
    $id = intval($data['id']);
    $conn->query("DELETE FROM listings WHERE id=$id");
    echo json_encode(["success" => true]);
  }
 
  elseif ($action === 'banUser') {
    $id = intval($data['id']);
    $conn->query("DELETE FROM users WHERE id=$id AND role != 'admin'");
    echo json_encode(["success" => true]);
  }
 
  elseif ($action === 'deleteInquiry') {
    $id = intval($data['id']);
    $conn->query("DELETE FROM inquiries WHERE id=$id");
    echo json_encode(["success" => true]);
  }
}
?>