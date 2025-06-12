<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $il = trim($_POST['il']);
    $ilce = trim($_POST['ilce']);
    $semt = trim($_POST['semt']);
    $mahalle = trim($_POST['mahalle']);

    // Validate input
    if (empty($il) || empty($ilce) || empty($mahalle)) {
        $error_message = 'İl, ilçe ve mahalle alanları zorunludur.';
    } else {
        try {
            // Check if location already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM locations WHERE il = ? AND ilce = ? AND mahalle = ?");
            $stmt->execute([$il, $ilce, $mahalle]);
            $exists = $stmt->fetchColumn() > 0;

            if ($exists) {
                $error_message = 'Bu lokasyon zaten mevcut.';
            } else {
                // Insert new location
                $stmt = $conn->prepare("INSERT INTO locations (il, ilce, semt, mahalle) VALUES (?, ?, ?, ?)");
                $stmt->execute([$il, $ilce, $semt, $mahalle]);
                $success_message = 'Lokasyon başarıyla eklendi.';
                
                // Redirect after successful insertion
                header('Location: admin_locations.php?success=1');
                exit();
            }
        } catch (PDOException $e) {
            $error_message = 'Bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Lokasyon Ekle - Bakbii Emlak</title>
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
                    <a href="admin_users.php" class="nav-link">
                        <i class="bi bi-people"></i> Kullanıcılar
                    </a>
                    <a href="admin_listings.php" class="nav-link">
                        <i class="bi bi-house-door"></i> İlanlar
                    </a>
                    <a href="admin_messages.php" class="nav-link">
                        <i class="bi bi-chat-dots"></i> Mesajlar
                    </a>
                    <a href="admin_locations.php" class="nav-link active">
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
                    <h2>Yeni Lokasyon Ekle</h2>
                    <a href="admin_locations.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Geri Dön</a>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="il" class="form-label">İl *</label>
                                <input type="text" class="form-control" id="il" name="il" required value="<?php echo isset($_POST['il']) ? htmlspecialchars($_POST['il']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="ilce" class="form-label">İlçe *</label>
                                <input type="text" class="form-control" id="ilce" name="ilce" required value="<?php echo isset($_POST['ilce']) ? htmlspecialchars($_POST['ilce']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="semt" class="form-label">Semt</label>
                                <input type="text" class="form-control" id="semt" name="semt" value="<?php echo isset($_POST['semt']) ? htmlspecialchars($_POST['semt']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="mahalle" class="form-label">Mahalle *</label>
                                <input type="text" class="form-control" id="mahalle" name="mahalle" required value="<?php echo isset($_POST['mahalle']) ? htmlspecialchars($_POST['mahalle']) : ''; ?>">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Lokasyon Ekle</button>
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