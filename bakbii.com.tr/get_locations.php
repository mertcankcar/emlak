<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

if ($limit <= 0 || $limit > 200) {
    $limit = 50;
}

$offset = ($page - 1) * $limit;

switch ($action) {
    case 'provinces':
    $stmt = $conn->prepare("SELECT DISTINCT il FROM locations ORDER BY il LIMIT ?, ?");
    $stmt->execute([$offset, $limit]);
    $countStmt = $conn->prepare("SELECT COUNT(DISTINCT il) FROM locations");
    $countStmt->execute();
        if (!empty($search)) {
            $stmt = $conn->prepare("SELECT id, name FROM provinces WHERE name LIKE ? ORDER BY name LIMIT ?, ?");
            $stmt->execute(["%$search%", $offset, $limit]);
            
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM provinces WHERE name LIKE ?");
            $countStmt->execute(["%$search%"]);
        } else {
            $stmt = $conn->prepare("SELECT id, name FROM provinces ORDER BY name LIMIT ?, ?");
            $stmt->execute([$offset, $limit]);
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM provinces");
            $countStmt->execute();
        }
        
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $limit);
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = ['id' => $row['id'], 'name' => $row['name']];
        }
        
        echo json_encode([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'limit' => $limit
            ]
        ]);
        break;
        
    case 'districts':
        $province = $parent_id;
        if (!empty($search)) {
            $stmt = $conn->prepare("SELECT id, name FROM districts WHERE province_id = ? AND name LIKE ? ORDER BY name LIMIT ?, ?");
            $stmt->execute([$province, "%$search%", $offset, $limit]);
            
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM districts WHERE province_id = ? AND name LIKE ?");
            $countStmt->execute([$province, "%$search%"]);
        } else {
            $stmt = $conn->prepare("SELECT id, name FROM districts WHERE province_id = ? ORDER BY name LIMIT ?, ?");
            $stmt->execute([$province, $offset, $limit]);
            
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM districts WHERE province_id = ?");
            $countStmt->execute([$province]);
        }
        
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $limit);
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = ['id' => $row['id'], 'name' => $row['name']];
        }
        
        echo json_encode([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'limit' => $limit
            ]
        ]);
        break;
        
    case 'neighborhoods':
        $district = $parent_id;
        if (!empty($search)) {
            $stmt = $conn->prepare("SELECT id, name FROM neighborhoods WHERE district_id = ? AND name LIKE ? ORDER BY name LIMIT ?, ?");
            $stmt->execute([$district, "%$search%", $offset, $limit]);
            
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM neighborhoods WHERE district_id = ? AND name LIKE ?");
            $countStmt->execute([$district, "%$search%"]);
        } else {
            $stmt = $conn->prepare("SELECT id, name FROM neighborhoods WHERE district_id = ? ORDER BY name LIMIT ?, ?");
            $stmt->execute([$district, $offset, $limit]);
            
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM neighborhoods WHERE district_id = ?");
            $countStmt->execute([$district]);
        }
        
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $limit);
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = ['id' => $row['id'], 'name' => $row['name']];
        }
        
        echo json_encode([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'limit' => $limit
            ]
        ]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>