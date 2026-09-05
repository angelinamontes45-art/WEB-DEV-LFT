<?php
require '../Includes/db.php';
startUserSession();

if (currentUser()) {
    $existingUser = currentUser();
    $home = $existingUser['role'] === 'admin' ? '../Admin/index.php' : ($existingUser['role'] === 'staff' ? '../Staff/index.php' : '../Dashboard/index.php');
    header('Location: ' . $home);
    exit;
}

$errors = [];
$mode = ($_GET['mode'] ?? 'login') === 'register' ? 'register' : 'login';
$next = safeNextPath($_GET['next'] ?? '../Dashboard/index.php');
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $mode = ($_POST['mode'] ?? 'login') === 'register' ? 'register' : 'login';
    $next = safeNextPath($_POST['next'] ?? '../Dashboard/index.php');
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'started' => time()];
    if (($attempts['started'] ?? 0) < time() - 900) $attempts = ['count' => 0, 'started' => time()];
    if ($mode === 'login' && ($attempts['count'] ?? 0) >= 8) {
        $errors[] = 'Too many login attempts. Please try again later.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($mode === 'register' && mb_strlen($name) < 2) $errors[] = 'Enter your full name.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

    if (!$errors) {
        if ($mode === 'register') {
            try {
                $statement = db()->prepare('INSERT INTO users (name, email, password_hash, role, updated_at) VALUES (?, ?, ?, "customer", ?)');
                $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), appNow()->format('Y-m-d H:i:s')]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)db()->lastInsertId();
                $_SESSION['login_attempts'] = ['count' => 0, 'started' => time()];
                header('Location: ' . $next);
                exit;
            } catch (PDOException $exception) {
                $errors[] = str_contains(strtolower($exception->getMessage()), 'unique') ? 'That email is already registered.' : 'We could not create your account.';
            }
        } else {
            $statement = db()->prepare('SELECT id, password_hash, role FROM users WHERE email = ?');
            $statement->execute([$email]);
            $user = $statement->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['login_attempts'] = ['count' => 0, 'started' => time()];
                $home = $user['role'] === 'admin' ? '../Admin/index.php' : ($user['role'] === 'staff' ? '../Staff/index.php' : $next);
                header('Location: ' . $home);
                exit;
            }
            $attempts['count'] = ($attempts['count'] ?? 0) + 1;
            $_SESSION['login_attempts'] = $attempts;
            $errors[] = 'Email or password is incorrect.';
        }
    }
}

$pageTitle = ($mode === 'register' ? 'Create Account' : 'Log In') . ' | LFT Dumaguete';
$currentPage = 'LOGIN';
require '../Includes/header.php';
?>
<main>
    <section class="auth-section">
        <div class="auth-card">
            <p class="section-label">LFT MEMBER ACCESS</p>
            <h1><?= $mode === 'register' ? 'Create your account.' : 'Welcome back.' ?></h1>
            <p><?= $mode === 'register' ? 'Create an account to book a space or check in as a walk-in.' : 'Log in to manage your bookings and walk-in visits.' ?></p>
            <?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?>
            <form class="tour-form" method="post">
                <?= csrfField() ?>
                <input type="hidden" name="mode" value="<?= e($mode) ?>">
                <input type="hidden" name="next" value="<?= e($next) ?>">
                <?php if ($mode === 'register'): ?>
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" maxlength="120" autocomplete="name" value="<?= e($name) ?>" required>
                <?php endif; ?>
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" maxlength="190" autocomplete="email" value="<?= e($email) ?>" required>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" minlength="8" autocomplete="<?= $mode === 'register' ? 'new-password' : 'current-password' ?>" required>
                <button class="btn btn-green" type="submit"><?= $mode === 'register' ? 'CREATE ACCOUNT' : 'LOG IN' ?></button>
            </form>
            <p class="auth-switch"><?= $mode === 'register' ? 'Already a member?' : 'New to LFT?' ?> <a href="?mode=<?= $mode === 'register' ? 'login' : 'register' ?>&next=<?= urlencode($next) ?>"><?= $mode === 'register' ? 'Log in' : 'Create an account' ?></a></p>
        </div>
    </section>
</main>
<?php require '../Includes/footer.php'; ?>
