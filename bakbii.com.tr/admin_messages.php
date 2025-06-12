<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE (m.message LIKE ? OR u_sender.username LIKE ? OR u_receiver.username LIKE ?)"; 
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total number of messages for pagination
$count_sql = "SELECT COUNT(*) FROM messages m $where_clause";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get messages with pagination
$sql = "SELECT m.*, 
        u_sender.username as sender_username, 
        u_receiver.username as receiver_username
        FROM messages m 
        JOIN users u_sender ON m.sender_id = u_sender.id 
        JOIN users u_receiver ON m.receiver_id = u_receiver.id 
        $where_clause 
        ORDER BY m.created_at DESC 
        LIMIT $offset, $records_per_page";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesaj Yönetimi - Bakbii Emlak</title>
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
        .message-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                    <a href="admin_messages.php" class="nav-link active">
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
                    <h2>Mesaj Yönetimi</h2>
                </div>
                
                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Mesaj içeriği veya kullanıcı ara..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Ara</button>
                                </div>
                            </div>
                            <?php if (!empty($search)): ?>
                                <div class="col-md-2">
                                    <a href="admin_messages.php" class="btn btn-secondary">Filtreyi Temizle</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Messages Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Gönderen</th>
                                        <th>Alıcı</th>
                                        <th>İlan</th>
                                        <th>Mesaj</th>
                                        <th>Tarih</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($messages)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Mesaj bulunamadı</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($messages as $message): ?>
                                            <tr>
                                                <td><?php echo $message['id']; ?></td>
                                                <td><?php echo htmlspecialchars($message['sender_username']); ?></td>
                                                <td><?php echo htmlspecialchars($message['receiver_username']); ?></td>
                                                <td>
                                                    <span class="text-muted">-</span>
                                                </td>
                                                <td class="message-preview"><?php echo htmlspecialchars($message['message']); ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?></td>
                                                <td>
                                                    <a href="admin_view_message.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                                    <a href="admin_messages.php?delete_id=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu mesajı silmek istediğinizden emin misiniz?')"><i class="bi bi-trash"></i></a>
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
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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