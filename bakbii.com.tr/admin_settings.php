<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Initialize messages
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Site title update
    if (isset($_POST['site_title'])) {
        $site_title = trim($_POST['site_title']);
        
        try {
            // Check if setting exists
            $stmt = $conn->prepare("SELECT * FROM site_settings WHERE setting_name = 'site_title'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update existing setting
                $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_name = 'site_title'");
                $stmt->execute([$site_title]);
            } else {
                // Insert new setting
                $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value, created_at, updated_at) VALUES ('site_title', ?, NOW(), NOW())");
                $stmt->execute([$site_title]);
            }
            
            $success_message = 'Site başlığı başarıyla güncellendi.';
        } catch (PDOException $e) {
            $error_message = 'Site başlığı güncellenirken bir hata oluştu: ' . $e->getMessage();
        }
    }
    
    // Contact email update
    if (isset($_POST['contact_email'])) {
        $contact_email = trim($_POST['contact_email']);
        
        if (filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            try {
                // Check if setting exists
                $stmt = $conn->prepare("SELECT * FROM site_settings WHERE setting_name = 'contact_email'");
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    // Update existing setting
                    $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_name = 'contact_email'");
                    $stmt->execute([$contact_email]);
                } else {
                    // Insert new setting
                    $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value, created_at, updated_at) VALUES ('contact_email', ?, NOW(), NOW())");
                    $stmt->execute([$contact_email]);
                }
                
                $success_message = 'İletişim e-postası başarıyla güncellendi.';
            } catch (PDOException $e) {
                $error_message = 'İletişim e-postası güncellenirken bir hata oluştu: ' . $e->getMessage();
            }
        } else {
            $error_message = 'Geçerli bir e-posta adresi giriniz.';
        }
    }
    
    // Phone number update
    if (isset($_POST['phone_number'])) {
        $phone_number = trim($_POST['phone_number']);
        
        try {
            // Check if setting exists
            $stmt = $conn->prepare("SELECT * FROM site_settings WHERE setting_name = 'phone_number'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update existing setting
                $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_name = 'phone_number'");
                $stmt->execute([$phone_number]);
            } else {
                // Insert new setting
                $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value, created_at, updated_at) VALUES ('phone_number', ?, NOW(), NOW())");
                $stmt->execute([$phone_number]);
            }
            
            $success_message = 'Telefon numarası başarıyla güncellendi.';
        } catch (PDOException $e) {
            $error_message = 'Telefon numarası güncellenirken bir hata oluştu: ' . $e->getMessage();
        }
    }
    
    // Address update
    if (isset($_POST['address'])) {
        $address = trim($_POST['address']);
        
        try {
            // Check if setting exists
            $stmt = $conn->prepare("SELECT * FROM site_settings WHERE setting_name = 'address'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update existing setting
                $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_name = 'address'");
                $stmt->execute([$address]);
            } else {
                // Insert new setting
                $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value, created_at, updated_at) VALUES ('address', ?, NOW(), NOW())");
                $stmt->execute([$address]);
            }
            
            $success_message = 'Adres başarıyla güncellendi.';
        } catch (PDOException $e) {
            $error_message = 'Adres güncellenirken bir hata oluştu: ' . $e->getMessage();
        }
    }
}

// Get current settings
try {
    $stmt = $conn->prepare("SELECT * FROM site_settings");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // If table doesn't exist, create it
    if ($e->getCode() == '42S02') {
        try {
            $conn->exec("CREATE TABLE site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_name VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Initialize with default values
            $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES 
                ('site_title', 'Bakbii Emlak'),
                ('contact_email', 'info@bakbii.com'),
                ('phone_number', '+90 555 123 4567'),
                ('address', 'İstanbul, Türkiye')
            ");
            $stmt->execute();
            
            // Get the settings again
            $stmt = $conn->prepare("SELECT * FROM site_settings");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e2) {
            $error_message = 'Ayarlar tablosu oluşturulurken bir hata oluştu: ' . $e2->getMessage();
            $settings = [];
        }
    } else {
        $error_message = 'Ayarlar alınırken bir hata oluştu: ' . $e->getMessage();
        $settings = [];
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Ayarları - Bakbii Emlak</title>
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
                    <a href="admin_locations.php" class="nav-link">
                        <i class="bi bi-geo-alt"></i> Lokasyonlar
                    </a>
                    <a href="admin_settings.php" class="nav-link active">
                        <i class="bi bi-gear"></i> Ayarlar
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Site Ayarları</h2>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="site_title" class="form-label">Site Başlığı</label>
                                <input type="text" class="form-control" id="site_title" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? 'Bakbii Emlak'); ?>" required>
                                <div class="form-text">Sitenin başlığı tarayıcı sekmesinde ve başlık kısmında görünür.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">İletişim E-posta Adresi</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'info@bakbii.com'); ?>" required>
                                <div class="form-text">İletişim formundan gelen mesajlar bu adrese iletilecektir.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Telefon Numarası</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($settings['phone_number'] ?? '+90 555 123 4567'); ?>">
                                <div class="form-text">İletişim sayfasında görüntülenecek telefon numarası.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Adres</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($settings['address'] ?? 'İstanbul, Türkiye'); ?></textarea>
                                <div class="form-text">İletişim sayfasında görüntülenecek adres bilgisi.</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Ayarları Kaydet</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>