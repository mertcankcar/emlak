-- Create the locations table with explicit UTF-8 encoding and collation
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sehir VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    ilce VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    semt VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    mahalle VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data from provinces, districts, and neighborhoods tables
INSERT INTO locations (sehir, ilce, semt, mahalle)
SELECT 
    p.name as sehir,
    d.name as ilce,
    '' as semt,
    n.name as mahalle
FROM 
    provinces p
    JOIN districts d ON p.id = d.province_id
    JOIN neighborhoods n ON d.id = n.district_id;