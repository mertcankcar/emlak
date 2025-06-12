<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_type = trim($_POST['user_type']);

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Lütfen tüm zorunlu alanları doldurunuz.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir email adresi giriniz.';
    } elseif ($password !== $confirm_password) {
        $error = 'Şifreler eşleşmiyor.';
    } elseif (strlen($password) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır.';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Bu kullanıcı adı veya email zaten kullanılıyor.';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type, created_at) VALUES (?, ?, ?, ?, NOW())");
            
            try {
                $stmt->execute([$username, $email, $hashed_password, $user_type]);
                $success = 'Kullanıcı başarıyla oluşturuldu.';
                // Clear form data after successful submission
                $username = $email = $password = $confirm_password = '';
                $user_type = 'individual';
            } catch(PDOException $e) {
                $error = 'Kullanıcı oluşturulurken bir hata oluştu.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Kullanıcı Ekle - Bakbii Emlak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/enhanced-styles.css">
    <style>
        .admin-sidebar {
            background-color: #2c3e50;
            color: white;
            min-height: calc(100vh - 56px);
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .admin-sidebar .nav-link i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="096bbb_3cdeffa1effd4cd5ac1857edda89442d~mv2 (1).jpg" alt="Bakbii Emlak" style="height: 30px; margin-right: 5px;">
                Bakbii Emlak
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php"><i class="bi bi-house"></i> Ana Sayfa</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar p-0">
                <div class="p-3">
                    <h5>Yönetici Paneli</h5>
                </div>
                <div class="nav flex-column p-3">
                    <a href="admin.php" class="nav-link">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="admin_users.php" class="nav-link active">
                        <i class="bi bi-people"></i> Kullanıcılar
                    </a>
                    <a href="admin_listings.php" class="nav-link">
                        <i class="bi bi-house-door"></i> İlanlar
                    </a>
                    <a href="admin_messages.php" class="nav-link">
                        <i class="bi bi-chat-dots"></i> Mesajlar
                    </a>
                    <a href="admin_locations.php" class="nav-link">
                        <i class="bi bi-geo-alt"></i> Lokasyonlar
                    </a>
                    <a href="admin_settings.php" class="nav-link">
                        <i class="bi bi-gear"></i> Ayarlar
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Yeni Kullanıcı Ekle</h2>
                    <a href="admin_users.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kullanıcılara Dön</a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Şifre en az 6 karakter olmalıdır.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Şifre Tekrar</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="user_type" class="form-label">Hesap Türü</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="individual" <?php echo (isset($user_type) && $user_type === 'individual') ? 'selected' : ''; ?>>Bireysel Kullanıcı</option>
                                    <option value="agency" <?php echo (isset($user_type) && $user_type === 'agency') ? 'selected' : ''; ?>>Emlak Ofisi</option>
                                    <option value="admin" <?php echo (isset($user_type) && $user_type === 'admin') ? 'selected' : ''; ?>>Yönetici</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="admin_users.php" class="btn btn-secondary me-md-2">İptal</a>
                                <button type="submit" class="btn btn-success">Kullanıcı Oluştur</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>