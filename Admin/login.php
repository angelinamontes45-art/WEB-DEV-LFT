<?php
require '../Includes/db.php';
startUserSession();
$user = currentUser();
if ($user && $user['role'] === 'admin') {
	header('Location: ./index.php');
	exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = strtolower(trim($_POST['email'] ?? ''));
	$password = $_POST['password'] ?? '';
	$statement = db()->prepare('SELECT id, password_hash, role FROM users WHERE email = ?');
	$statement->execute([$email]);
	$account = $statement->fetch();
	if ($account && $account['role'] === 'admin' && password_verify($password, $account['password_hash'])) {
		session_regenerate_id(true);
		$_SESSION['user_id'] = (int) $account['id'];
		header('Location: ./index.php');
		exit;
	}
	$error = 'Admin email or password is incorrect.';
}

$pageTitle = 'Admin Login | LFT Dumaguete';
$currentPage = 'CONTACT';
require '../Includes/header.php';
?>
<main class="admin-login-page"><section class="admin-login-hero"><div class="admin-login-intro"><p class="section-label">LFT DUMAGUETE · ADMIN</p><h1>Run the space<br><span>with intention.</span></h1><p>Manage bookings, customers, spaces, and events from one secure workspace.</p></div><div class="admin-login-card"><div class="admin-login-mark"><i class="fa-solid fa-lock"></i></div><p class="section-label">CONTROL CENTER</p><h2>Admin sign in</h2><p>Authorized administrators only.</p><?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?><form class="tour-form" method="post"><label for="email">Admin email</label><input id="email" name="email" type="email" autocomplete="username" required><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required><button class="btn btn-green" type="submit">SIGN IN TO ADMIN</button></form><a class="admin-back-link" href="../index.php"><i class="fa-solid fa-arrow-left"></i> Back to public website</a></div></section></main>
<?php require '../Includes/footer.php'; ?>
