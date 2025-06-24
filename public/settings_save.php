  <?php
  session_start();
  require 'connect.php';
  require 'auth.php';

  $user_id = $_SESSION['user_id'];

  // Checkbox verileri yoksa sıfır say (unchecked)
  $is_profile_public = isset($_POST['is_profile_public']) ? 1 : 0;
  $is_files_public   = isset($_POST['is_files_public']) ? 1 : 0;
  $stmt = $pdo->prepare("UPDATE users SET is_profile_public = ?, is_files_public = ?  WHERE user_id = ?");
  $stmt->execute([$is_profile_public, $is_files_public , $user_id]);

  header("Location: settings.php");
  exit;
  ?>