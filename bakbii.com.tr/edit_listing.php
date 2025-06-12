<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: my_listings.php');
    exit();
}

$listing_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the listing details
$stmt = $conn->prepare("SELECT * FROM property_listings WHERE id = ? AND user_id = ?");
$stmt->execute([$listing_id, $user_id]);
$listing = $stmt->fetch();

if (!$listing) {
    header('Location: my_listings.php');
    exit();
}

// Fetch existing images
$stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ?");
$stmt->execute([$listing_id]);
$existing_images = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $address = trim($_POST['address']);
    $phone_number = trim($_POST['phone_number']);

    if (empty($title) || empty($description) || empty($price) || empty($address) || empty($phone_number)) {
        $error = 'Tüm alanları doldurunuz.';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Geçerli bir fiyat giriniz.';
    } else {
        try {
            $conn->beginTransaction();
            
            // Update property listing
            $stmt = $conn->prepare("UPDATE property_listings SET title = ?, description = ?, price = ?, address = ?, phone_number = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $price, $address, $phone_number, $listing_id, $user_id]);

            // Handle new image uploads
            if (!empty($_FILES['photos']['name'][0])) {
                $upload_dir = 'uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['photos']['error'][$key] === 0) {
                        $file_extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                        $new_filename = uniqid() . '.' . $file_extension;
                        $destination = $upload_dir . $new_filename;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            $stmt = $conn->prepare("INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?, ?, ?)");
                            $is_primary = empty($existing_images) ? true : false;
                            $stmt->execute([$listing_id, $destination, $is_primary]);
                        }
                    }
                }
            }

            // Handle image deletions
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    $stmt = $conn->prepare("SELECT image_path FROM property_images WHERE id = ? AND property_id = ?");
                    $stmt->execute([$image_id, $listing_id]);
                    $image = $stmt->fetch();

                    if ($image && file_exists($image['image_path'])) {
                        unlink($image['image_path']);
                    }

                    $stmt = $conn->prepare("DELETE FROM property_images WHERE id = ? AND property_id = ?");
                    $stmt->execute([$image_id, $listing_id]);
                }
            }

            $conn->commit();
            $success = 'İlan başarıyla güncellendi!';

            // Refresh listing and images data
            $stmt = $conn->prepare("SELECT * FROM property_listings WHERE id = ? AND user_id = ?");
            $stmt->execute([$listing_id, $user_id]);
            $listing = $stmt->fetch();

            $stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ?");
            $stmt->execute([$listing_id]);
            $existing_images = $stmt->fetchAll();
        } catch(Exception $e) {
            $conn->rollBack();
            $error = 'İlan güncellenirken bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Düzenle - Bakbii Emlak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/enhanced-styles.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center mb-0">İlan Düzenle</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">İlan Başlığı</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">İlan Açıklaması</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($listing['description']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Fiyat (TL)</label>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars($listing['price']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Adres</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($listing['address']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Telefon Numarası</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" pattern="[0-9]{10,11}" value="<?php echo htmlspecialchars($listing['phone_number']); ?>" required>
                                <div class="form-text">Örnek: 05xxxxxxxxx</div>
                            </div>

                            <?php if (!empty($existing_images)): ?>
                            <div class="mb-3">
                                <label class="form-label">Mevcut Fotoğraflar</label>
                                <div class="existing-images">
                                    <?php foreach ($existing_images as $image): ?>
                                        <div class="existing-image">
                                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Property Image">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="delete_images[]" value="<?php echo $image['id']; ?>" id="delete_image_<?php echo $image['id']; ?>">
                                                <label class="form-check-label" for="delete_image_<?php echo $image['id']; ?>">
                                                    Sil
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="photos" class="form-label">Yeni Fotoğraflar Ekle</label>
                                <input type="file" class="form-control" id="photos" name="photos[]" accept="image/*" multiple>
                                <div class="form-text">En fazla 5 fotoğraf yükleyebilirsiniz</div>
                                <div id="imagePreviewContainer" class="image-preview-container"></div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                                <a href="my_listings.php" class="btn btn-outline-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('photos').addEventListener('change', function(event) {
            const container = document.getElementById('imagePreviewContainer');
            container.innerHTML = '';
            const files = event.target.files;

            for (let i = 0; i < Math.min(files.length, 5); i++) {
                const file = files[i];
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('image-preview');
                    container.appendChild(img);
                }

                reader.readAsDataURL(file);
            }
        });

        // Form validation
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })();
    </script>
</body>
</html>