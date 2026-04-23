
<?php
require_once 'config.php';
 
$method = $_SERVER['REQUEST_METHOD'];
 
if ($method === 'POST') {
  $data = json_decode(file_get_contents("php://input"), true);
  $action = $data['action'] ?? '';
 
  if ($action === 'register') {
    $name       = $conn->real_escape_string($data['name']);
    $student_id = $conn->real_escape_string($data['student_id']);
    $email      = $conn->real_escape_string($data['email']);
    $department = $conn->real_escape_string($data['department']);
    $phone      = $conn->real_escape_string($data['phone'] ?? '');
    $password   = password_hash($data['password'], PASSWORD_DEFAULT);
 
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
      echo json_encode(["success" => false, "message" => "Email already registered"]);
      exit;
    }
    $conn->query("INSERT INTO users (name, student_id, email, department, phone, password, role) VALUES ('$name','$student_id','$email','$department','$phone','$password','user')");
    $id = $conn->insert_id;
    echo json_encode(["success" => true, "user" => ["id" => $id, "name" => $name, "email" => $email, "student_id" => $student_id, "department" => $department, "phone" => $phone, "role" => "user"]]);
  }
 
  elseif ($action === 'login') {
    $email    = $conn->real_escape_string($data['email']);
    $password = $data['password'];
    $res = $conn->query("SELECT * FROM users WHERE email='$email' OR student_id='$email'");
    if ($res->num_rows === 0) { echo json_encode(["success" => false, "message" => "User not found"]); exit; }
    $user = $res->fetch_assoc();
    if (!password_verify($password, $user['password'])) { echo json_encode(["success" => false, "message" => "Wrong password"]); exit; }
    unset($user['password']);
    echo json_encode(["success" => true, "user" => $user]);
  }
 
  elseif ($action === 'updateProfile') {
    $id         = intval($data['id']);
    $name       = $conn->real_escape_string($data['name']);
    $phone      = $conn->real_escape_string($data['phone']);
    $department = $conn->real_escape_string($data['department']);
    $conn->query("UPDATE users SET name='$name', phone='$phone', department='$department' WHERE id=$id");
    echo json_encode(["success" => true]);
  }
 
  elseif ($action === 'toggleWish') {
    $user_id    = intval($data['user_id']);
    $listing_id = intval($data['listing_id']);
    $check = $conn->query("SELECT id FROM wishlist WHERE user_id=$user_id AND listing_id=$listing_id");
    if ($check->num_rows > 0) {
      $conn->query("DELETE FROM wishlist WHERE user_id=$user_id AND listing_id=$listing_id");
      echo json_encode(["added" => false]);
    } else {
      $conn->query("INSERT INTO wishlist (user_id, listing_id) VALUES ($user_id, $listing_id)");
      echo json_encode(["added" => true]);
    }
  }
}
 
elseif ($method === 'GET') {
  $action = $_GET['action'] ?? '';
  if ($action === 'getWishlist') {
    $user_id = intval($_GET['user_id']);
    $res = $conn->query("SELECT l.*, u.name as seller_name, u.department as seller_dept, u.phone as seller_phone FROM listings l JOIN wishlist w ON l.id=w.listing_id JOIN users u ON l.seller_id=u.id WHERE w.user_id=$user_id");
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
  }
}
?>