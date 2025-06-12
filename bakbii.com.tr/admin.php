<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// If user is not an admin, redirect to index page
if ($_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get counts for dashboard
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE user_type != 'admin'");
$stmt->execute();
$total_users = $stmt->fetch()['total_users'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_listings FROM property_listings");
$stmt->execute();
$total_listings = $stmt->fetch()['total_listings'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_messages FROM messages");
$stmt->execute();
$total_messages = $stmt->fetch()['total_messages'] ?? 0;

// Get recent listings
$stmt = $conn->prepare("SELECT p.*, u.username FROM property_listings p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");
$stmt->execute();
$recent_listings = $stmt->fetchAll();

// Get recent users
$stmt = $conn->prepare("SELECT * FROM users WHERE user_type != 'admin' ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Bakbii Emlak</title>
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
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
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
                    <a href="admin.php" class="nav-link active">
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
                    <a href="admin_settings.php" class="nav-link">
                        <i class="bi bi-gear"></i> Ayarlar
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Dashboard</h2>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="bi bi-people card-icon"></i>
                                <h5 class="card-title">Toplam Kullanıcı</h5>
                                <h2><?php echo $total_users; ?></h2>
                                <a href="admin_users.php" class="btn btn-outline-light btn-sm mt-2">Tüm Kullanıcılar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="bi bi-house-door card-icon"></i>
                                <h5 class="card-title">Toplam İlan</h5>
                                <h2><?php echo $total_listings; ?></h2>
                                <a href="admin_listings.php" class="btn btn-outline-light btn-sm mt-2">Tüm İlanlar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="bi bi-chat-dots card-icon"></i>
                                <h5 class="card-title">Toplam Mesaj</h5>
                                <h2><?php echo $total_messages; ?></h2>
                                <a href="admin_messages.php" class="btn btn-outline-light btn-sm mt-2">Tüm Mesajlar</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Listings -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Son Eklenen İlanlar</h5>
                        <a href="admin_listings.php" class="btn btn-sm btn-primary">Tümünü Gör</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Başlık</th>
                                        <th>Fiyat</th>
                                        <th>Kullanıcı</th>
                                        <th>Tarih</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_listings as $listing): ?>
                                    <tr>
                                        <td><?php echo $listing['id']; ?></td>
                                        <td><?php echo htmlspecialchars($listing['title']); ?></td>
                                        <td><?php echo number_format($listing['price'], 2); ?> TL</td>
                                        <td><?php echo htmlspecialchars($listing['username']); ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($listing['created_at'])); ?></td>
                                        <td>
                                            <a href="listing_detail.php?id=<?php echo $listing['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                            <a href="admin_edit_listing.php?id=<?php echo $listing['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                            <a href="admin_delete_listing.php?id=<?php echo $listing['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu ilanı silmek istediğinizden emin misiniz?')"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Son Kayıt Olan Kullanıcılar</h5>
                        <a href="admin_users.php" class="btn btn-sm btn-primary">Tümünü Gör</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kullanıcı Adı</th>
                                        <th>Email</th>
                                        <th>Hesap Türü</th>
                                        <th>Kayıt Tarihi</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['user_type'] === 'individual'): ?>
                                                <span class="badge bg-primary">Bireysel</span>
                                            <?php elseif ($user['user_type'] === 'agency'): ?>
                                                <span class="badge bg-success">Emlak Ofisi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                            <a href="admin_delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>