
<?php
require_once 'config.php';
 
$method = $_SERVER['REQUEST_METHOD'];
 
if ($method === 'POST') {
  $data       = json_decode(file_get_contents("php://input"), true);
  $listing_id = intval($data['listing_id']);
  $buyer_id   = intval($data['buyer_id']);
  $message    = $conn->real_escape_string($data['message']);
  $phone      = $conn->real_escape_string($data['phone'] ?? '');
  $conn->query("INSERT INTO inquiries (listing_id, buyer_id, message, phone) VALUES ($listing_id, $buyer_id, '$message', '$phone')");
  echo json_encode(["success" => true]);
}
 
elseif ($method === 'GET') {
  $action = $_GET['action'] ?? '';
  if ($action === 'getByUser') {
    $user_id = intval($_GET['user_id']);
    $res = $conn->query("SELECT i.*, l.title as listing_title FROM inquiries i JOIN listings l ON i.listing_id=l.id WHERE i.buyer_id=$user_id ORDER BY i.created_at DESC");
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
  }
}
?>