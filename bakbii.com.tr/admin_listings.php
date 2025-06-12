<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle listing deletion
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    try {
        // First delete associated images
        $stmt = $conn->prepare("DELETE FROM property_images WHERE property_id = ?");
        $stmt->execute([$delete_id]);
        
        // Then delete the listing
        $stmt = $conn->prepare("DELETE FROM property_listings WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success_message = 'İlan başarıyla silindi.';
    } catch (PDOException $e) {
        $error_message = 'İlan silinirken bir hata oluştu.';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Search and filter functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$property_type = isset($_GET['property_type']) ? trim($_GET['property_type']) : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ?)"; 
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($property_type)) {
    $where_conditions[] = "p.property_type = ?";
    $params[] = $property_type;
}

if ($min_price !== null) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
}

if ($max_price !== null) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

// Get total number of listings for pagination
$count_sql = "SELECT COUNT(*) FROM property_listings p $where_clause";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get listings with pagination
$sql = "SELECT p.*, u.username, pi.image_path 
        FROM property_listings p 
        JOIN users u ON p.user_id = u.id 
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = true 
        $where_clause 
        ORDER BY p.created_at DESC 
        LIMIT $offset, $records_per_page";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Yönetimi - Bakbii Emlak</title>
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
        .listing-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
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
                    <a href="admin_listings.php" class="nav-link active">
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
                    <h2>İlan Yönetimi</h2>
                    <a href="admin_add_listing.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Yeni İlan Ekle</a>
                </div>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Search and Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="İlan ara..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="property_type">
                                    <option value="">Tüm Türler</option>
                                    <option value="konut" <?php echo $property_type === 'konut' ? 'selected' : ''; ?>>Konut</option>
                                    <option value="isyeri" <?php echo $property_type === 'isyeri' ? 'selected' : ''; ?>>İşyeri</option>
                                    <option value="arsa" <?php echo $property_type === 'arsa' ? 'selected' : ''; ?>>Arsa</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="min_price" placeholder="Min Fiyat" value="<?php echo $min_price !== null ? $min_price : ''; ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="max_price" placeholder="Max Fiyat" value="<?php echo $max_price !== null ? $max_price : ''; ?>">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Ara</button>
                            </div>
                            <?php if (!empty($search) || !empty($property_type) || $min_price !== null || $max_price !== null): ?>
                                <div class="col-12">
                                    <a href="admin_listings.php" class="btn btn-secondary">Filtreleri Temizle</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Listings Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Görsel</th>
                                        <th>Başlık</th>
                                        <th>Fiyat</th>
                                        <th>Tür</th>
                                        <th>Kullanıcı</th>
                                        <th>Tarih</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($listings)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">İlan bulunamadı</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listings as $listing): ?>
                                            <tr>
                                                <td><?php echo $listing['id']; ?></td>
                                                <td>
                                                    <?php if ($listing['image_path']): ?>
                                                        <img src="<?php echo htmlspecialchars($listing['image_path']); ?>" class="listing-image" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                                                    <?php else: ?>
                                                        <img src="hata.jpg" class="listing-image" alt="No Image">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($listing['title']); ?></td>
                                                <td><?php echo number_format($listing['price'], 2); ?> TL</td>
                                                <td>
                                                    <?php if ($listing['property_type'] === 'konut'): ?>
                                                        <span class="badge bg-primary">Konut</span>
                                                    <?php elseif ($listing['property_type'] === 'isyeri'): ?>
                                                        <span class="badge bg-success">İşyeri</span>
                                                    <?php elseif ($listing['property_type'] === 'arsa'): ?>
                                                        <span class="badge bg-warning">Arsa</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($listing['property_type']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($listing['username']); ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($listing['created_at'])); ?></td>
                                                <td>
                                                    <a href="listing_detail.php?id=<?php echo $listing['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                                    <a href="admin_edit_listing.php?id=<?php echo $listing['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                    <a href="admin_listings.php?delete_id=<?php echo $listing['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu ilanı silmek istediğinizden emin misiniz?')"><i class="bi bi-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-4">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($property_type) ? '&property_type=' . urlencode($property_type) : ''; ?><?php echo $min_price !== null ? '&min_price=' . $min_price : ''; ?><?php echo $max_price !== null ? '&max_price=' . $max_price : ''; ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($property_type) ? '&property_type=' . urlencode($property_type) : ''; ?><?php echo $min_price !== null ? '&min_price=' . $min_price : ''; ?><?php echo $max_price !== null ? '&max_price=' . $max_price : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($property_type) ? '&property_type=' . urlencode($property_type) : ''; ?><?php echo $min_price !== null ? '&min_price=' . $min_price : ''; ?><?php echo $max_price !== null ? '&max_price=' . $max_price : ''; ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>