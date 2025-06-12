<?php
// This file provides instructions for uploading the il-ilce-semt-mahalle.sql file to cPanel

/*
CPanel Yükleme Talimatları
-------------------------

Kullanıcı Bilgileri:
- Kullanıcı Adı: bakbiico
- Alan Adı: bakbii.com.tr
- Giriş Dizini: /home/bakbiico
- Son Giriş IP: 109.228.193.234

Adım 1: cPanel'e Giriş Yapın
---------------------------
1. Web tarayıcınızda https://bakbii.com.tr/cpanel adresine gidin
2. Kullanıcı adı: bakbiico
3. Şifrenizi girin

Adım 2: phpMyAdmin'e Erişin
--------------------------
1. cPanel ana sayfasında "Veritabanları" bölümünü bulun
2. "phpMyAdmin" simgesine tıklayın

Adım 3: Veritabanını Seçin
-------------------------
1. Sol taraftaki listeden "bakbiico_bakbiiemlak" veritabanını seçin

Adım 4: Tabloları Oluşturun
--------------------------
1. Önce locations.sql dosyasındaki tabloları oluşturmanız gerekiyor
2. phpMyAdmin'de "SQL" sekmesine tıklayın
3. Aşağıdaki SQL kodunu yapıştırın ve çalıştırın:

CREATE TABLE provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    FOREIGN KEY (province_id) REFERENCES provinces(id)
);

CREATE TABLE neighborhoods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('neighborhood', 'village') NOT NULL,
    FOREIGN KEY (district_id) REFERENCES districts(id)
);

Adım 5: Veri Dosyasını Yükleyin
-----------------------------
1. il-ilce-semt-mahalle.sql dosyası MS SQL formatında olduğu için doğrudan yüklenemez
2. Bunun yerine, aşağıdaki PHP betiğini kullanarak verileri işleyebilirsiniz

Adım 6: Veri İşleme Betiğini Yükleyin
-----------------------------------
1. cPanel'de "Dosya Yöneticisi"ni açın
2. /home/bakbiico/public_html dizinine gidin
3. Aşağıdaki dosyaları yükleyin:
   - il-ilce-semt-mahalle.sql
   - populate_locations.php (aşağıda düzenlenmiş versiyonu)

Adım 7: Betik Dosyasını Düzenleyin
--------------------------------
Aşağıdaki kodu populate_locations.php dosyasına kaydedin:

<?php
// Veritabanı bağlantı bilgileri
$db_host = '212.68.34.228';
$db_user = 'bakbiico';
$db_pass = '3UYd*6o[wkC78S';
$db_name = 'bakbiico_bakbiiemlak';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->exec("SET NAMES utf8mb4");
    
    // Mevcut verileri temizle
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    $conn->exec("TRUNCATE TABLE neighborhoods");
    $conn->exec("TRUNCATE TABLE districts");
    $conn->exec("TRUNCATE TABLE provinces");
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

    // SQL dosyasını oku ve işle
    $sql_content = file_get_contents('il-ilce-semt-mahalle.sql');
    $lines = explode("\n", $sql_content);

    $provinces = [];
    $districts = [];
    $neighborhoods = [];
    $count = 0;

    foreach ($lines as $line) {
        if (strpos($line, 'INSERT') !== false) {
            // MS SQL formatındaki INSERT ifadesini analiz et
            preg_match("/\[dbo\]\.\[tCity_District_Street_Town\] \(\[id\], \[il\], \[ilce\], \[semt_bucak_belde\], \[mahalle\], \[posta_kodu\]\) VALUES \((.*?), N'(.*?)', N'(.*?)', N'(.*?)', N'(.*?)', N'(.*?)'\)/", $line, $matches);
            
            if (count($matches) >= 6) {
                $province = $matches[2];
                $district = $matches[3];
                $area = $matches[4];
                $neighborhood = $matches[5];

                // İl ekle (eğer yoksa)
                if (!isset($provinces[$province])) {
                    $stmt = $conn->prepare("INSERT INTO provinces (name) VALUES (?)");
                    $stmt->execute([$province]);
                    $provinces[$province] = $conn->lastInsertId();
                }

                // İlçe ekle (eğer yoksa)
                $district_key = $province . '-' . $district;
                if (!isset($districts[$district_key])) {
                    $stmt = $conn->prepare("INSERT INTO districts (province_id, name) VALUES (?, ?)");
                    $stmt->execute([$provinces[$province], $district]);
                    $districts[$district_key] = $conn->lastInsertId();
                }

                // Mahalle ekle
                $neighborhood_key = $district_key . '-' . $neighborhood;
                if (!isset($neighborhoods[$neighborhood_key])) {
                    $type = (strpos(strtolower($neighborhood), 'köy') !== false) ? 'village' : 'neighborhood';
                    $stmt = $conn->prepare("INSERT INTO neighborhoods (district_id, name, type) VALUES (?, ?, ?)");
                    $stmt->execute([$districts[$district_key], $neighborhood, $type]);
                    $neighborhoods[$neighborhood_key] = true;
                    $count++;
                }
            }
        }
    }

    echo "<h1>İşlem Tamamlandı</h1>";
    echo "<p>Toplam $count mahalle/köy eklendi.</p>";
    echo "<p>İl sayısı: " . count($provinces) . "</p>";
    echo "<p>İlçe sayısı: " . count($districts) . "</p>";
    echo "<p>Mahalle/Köy sayısı: $count</p>";
    
} catch (PDOException $e) {
    echo "<h1>Veritabanı Hatası</h1>";
    echo "<p>Hata: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<h1>Genel Hata</h1>";
    echo "<p>Hata: " . $e->getMessage() . "</p>";
}
?>

Adım 8: Betik Dosyasını Çalıştırın
--------------------------------
1. Web tarayıcınızda https://bakbii.com.tr/populate_locations.php adresine gidin
2. Betik çalışacak ve verileri veritabanına aktaracaktır
3. İşlem tamamlandığında bir onay mesajı göreceksiniz

Not: Bu işlem, dosyanın boyutuna bağlı olarak biraz zaman alabilir. Lütfen sabırlı olun ve işlem tamamlanana kadar sayfayı kapatmayın.
*/

echo "<h1>cPanel Yükleme Talimatları</h1>";
echo "<p>Bu dosya, il-ilce-semt-mahalle.sql dosyasını cPanel'e yükleme talimatlarını içerir.</p>";
echo "<p>Lütfen dosya içeriğini görüntülemek için bu dosyayı bir metin editöründe açın.</p>";
?>