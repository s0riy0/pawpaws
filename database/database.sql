-- PawPaws : ระบบ E-Commerce ขายสัตว์เลี้ยง
SET NAMES utf8mb4;
DROP DATABASE IF EXISTS pawpaws;
CREATE DATABASE pawpaws CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pawpaws;

CREATE TABLE users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(50)  NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  full_name   VARCHAR(100) NOT NULL,
  email       VARCHAR(100) NOT NULL UNIQUE,
  phone       VARCHAR(20),
  address     TEXT,
  birth_date  DATE NOT NULL,
  role        ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE pets (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  category_id  INT NOT NULL,
  name         VARCHAR(100) NOT NULL,
  breed        VARCHAR(100),
  gender       ENUM('male','female') NOT NULL,
  birth_date   DATE NOT NULL,
  price        DECIMAL(10,2) NOT NULL,
  description  TEXT,
  image        VARCHAR(255),
  status       ENUM('available','sold') NOT NULL DEFAULT 'available',
  created_by   INT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pet_media (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  pet_id      INT NOT NULL,
  type        ENUM('image','video') NOT NULL,
  file        VARCHAR(255) NOT NULL,
  caption     VARCHAR(255),
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
  user_id   INT NOT NULL,
  pet_id    INT NOT NULL,
  added_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, pet_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (pet_id)  REFERENCES pets(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  total           DECIMAL(10,2) NOT NULL,
  payment_method  ENUM('transfer','promptpay','card','cod') NOT NULL,
  ship_name       VARCHAR(100) NOT NULL,
  ship_phone      VARCHAR(20)  NOT NULL,
  ship_address    TEXT NOT NULL,
  status          ENUM('shipping','received') NOT NULL DEFAULT 'shipping',
  paid_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  received_at     DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  order_id  INT NOT NULL,
  pet_id    INT NOT NULL,
  price     DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (order_id, pet_id),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (pet_id)   REFERENCES pets(id)
) ENGINE=InnoDB;

CREATE TABLE messages (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  customer_id  INT NOT NULL,
  sender_id    INT NOT NULL,
  pet_id       INT NULL,
  body         TEXT NOT NULL,
  is_read      TINYINT(1) NOT NULL DEFAULT 0,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (pet_id)      REFERENCES pets(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES ('แมว'),('สุนัข'),('กระต่าย'),('Exotic');

INSERT INTO users (username,password,full_name,email,phone,address,birth_date,role) VALUES
('soriya-admin','$2y$10$JPnEGtYM28S/WGRbNB1Bz.Akk/7bsFoSXzCln8BGtBp09v57GWB7G','แอดมินโสริยา','admin@pawpaws.test','020000000','PawPaws HQ','1995-01-01','admin'),
('Nutchapol','$2y$10$xQLUl4SMTyiATvwA2bBjT.DEmoDEDceQsISs3PlnDKnRkqGWeuWZi','ปันปัน รักเด็ก','customer@pawpaws.test','0812345678','99 ถ.สุขุมวิท กรุงเทพฯ 10110','2000-05-15','customer');

INSERT INTO pets (category_id,name,breed,gender,birth_date,price,description,image,created_by) VALUES
(2,'บัดดี้','บีเกิล','male','2026-03-10',15900,'ขี้เล่น เป็นมิตรกับเด็ก ฉีดวัคซีนครบ','https://images.unsplash.com/photo-1606833695155-e3498374e701?auto=format&fit=crop&w=800&q=80',1),
(1,'มะลิ','สก็อตติช โฟลด์','female','2026-05-02',18500,'นิสัยอ่อนโยน ชอบนอนตัก','https://images.unsplash.com/photo-1552622175-99cca9acf74d?auto=format&fit=crop&w=800&q=80',1),
(1,'ส้มโอ','บริติช ช็อตแฮร์','male','2026-04-18',12000,'ขนแน่น ตาโต กินเก่ง','https://images.unsplash.com/photo-1739398612684-e5c3ac2fe54f?auto=format&fit=crop&w=800&q=80',1),
(2,'โมจิ','ชิบะ อินุ','female','2026-02-20',32000,'ร่าเริง ฉลาด ฝึกขับถ่ายแล้ว','https://images.unsplash.com/photo-1746738771391-33d44630f5f8?auto=format&fit=crop&w=800&q=80',1),
(3,'ปุยฝ้าย','ฮอลแลนด์ ลอป','female','2026-06-01',3500,'หูตก ขนนุ่ม เลี้ยงง่าย','https://images.unsplash.com/photo-1535241749838-299277b6305f?auto=format&fit=crop&w=800&q=80',1),
(4,'ลาวา','เบียร์ดดราก้อน','male','2025-12-12',6900,'เชื่อง ชอบอาบแดด','https://images.unsplash.com/photo-1530525393181-2483a388767e?auto=format&fit=crop&w=800&q=80',1);

INSERT INTO pet_media (pet_id,type,file,caption) SELECT id,'image',image,'รูปล่าสุดของน้อง' FROM pets WHERE image IS NOT NULL;

INSERT IGNORE INTO categories (name) VALUES ('นก'),('สัตว์ฟันแทะ');

INSERT INTO pets (category_id,name,breed,gender,birth_date,price,description,image,created_by) VALUES
((SELECT id FROM categories WHERE name='สุนัข'),'ลาเต้','ลาบราดอร์ รีทรีฟเวอร์','male','2026-04-05',22000,'ร่าเริง ชอบคาบของเล่น เข้ากับเด็กได้ดี','https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='สุนัข'),'ข้าวปั้น','ชิสุ','female','2026-05-20',14500,'ขนยาวนุ่ม นิสัยอ้อนเก่ง เหมาะกับคอนโด','https://images.unsplash.com/photo-1534361960057-19889db9621e?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='สุนัข'),'น้ำผึ้ง','โกลเด้น รีทรีฟเวอร์','female','2026-06-12',25000,'ใจดี ฉลาด ฝึกง่าย ฉีดวัคซีนเข็มแรกแล้ว','https://images.unsplash.com/photo-1600804340584-c7db2eacf0bf?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='สุนัข'),'มาร์ช','โกลเด้น รีทรีฟเวอร์','male','2026-05-28',24000,'ขี้เล่น ชอบวิ่งเล่นสนามหญ้า','https://images.unsplash.com/photo-1591160690555-5debfba289f0?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='สุนัข'),'หิมะ','ซามอยด์','female','2026-03-15',38000,'ยิ้มเก่ง ขนขาวฟู ต้องแปรงขนสม่ำเสมอ','https://images.unsplash.com/photo-1596492784531-6e6eb5ea9993?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='สุนัข'),'ถั่วดำ','พันธุ์ผสม','male','2026-01-30',4500,'ฉลาด ซื่อสัตย์ สุขภาพแข็งแรง','https://images.unsplash.com/photo-1518020382113-a7e8fc38eac9?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='แมว'),'ขนมปัง','เปอร์เซีย','male','2026-04-22',16500,'ขนยาวฟู นิ่ง ชอบนอนบนโซฟา','https://images.unsplash.com/photo-1592194996308-7b43878e84a6?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='แมว'),'ส้มจี๊ด','แมวส้ม (ไทย)','male','2026-06-01',2500,'ซน กินเก่ง ขี้อ้อนสุดๆ','https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='แมว'),'ซิลเวอร์','อเมริกัน ช็อตแฮร์','female','2026-05-10',15000,'ลายสลิดสีเงินสวย นิสัยเรียบร้อย','https://images.unsplash.com/photo-1574144611937-0df059b5ef3e?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='แมว'),'ปุยนุ่น','แมวขาว (ขาวมณี)','female','2026-06-18',9000,'ขนขาวทั้งตัว ตาสวย ขี้อาย','https://images.unsplash.com/photo-1561948955-570b270e7c36?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='แมว'),'ฟ้าใส','รัสเซียน บลู','male','2025-11-05',21000,'เท่ เงียบ ชอบถ่ายรูป','https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='แมว'),'แครอท','แมวส้มลายเสือ','male','2026-06-25',3000,'ชอบผจญภัยในสวน ปีนเก่ง','https://images.unsplash.com/photo-1560114928-40f1f1eb26a0?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='กระต่าย'),'ช็อกโก','กระต่ายพันธุ์ผสม','male','2026-04-14',1800,'ขนสีน้ำตาลช็อกโกแลต ชอบกินผัก','https://images.unsplash.com/photo-1452857297128-d9c29adba80b?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='กระต่าย'),'สโนว์','กระต่ายขาว','female','2026-05-03',2200,'ขาวสะอาด เชื่อง อุ้มได้','https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='สัตว์ฟันแทะ'),'ถั่วงอก','แฮมสเตอร์','male','2026-07-20',350,'ตัวเล็กจิ๋ว ชอบวิ่งวงล้อ เลี้ยงง่าย','https://images.unsplash.com/photo-1425082661705-1834bfd09dca?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='สัตว์ฟันแทะ'),'โมโม่','แกสบี้ (หนูตะเภา)','female','2026-06-08',900,'ส่งเสียงอี๊ดๆ เวลาดีใจ ชอบแครอท','https://images.unsplash.com/photo-1533152162573-93ad94eb20f6?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='สัตว์ฟันแทะ'),'คุกกี้','แกสบี้ (หนูตะเภา)','male','2026-06-08',900,'สีขาวดำ นิสัยใจเย็น','https://images.unsplash.com/photo-1618252903592-2b8c90c43a6e?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='นก'),'กีวี่','นกแก้วขนาดเล็ก','male','2026-02-11',2800,'สีเขียวเหลืองสดใส ร้องเพลงเก่ง','https://images.unsplash.com/photo-1552728089-57bdde30beb3?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='นก'),'มะม่วง','นกแก้วมาคอว์ (บลูแอนด์โกลด์)','female','2025-09-01',65000,'ฉลาดมาก เลียนเสียงพูดได้ อายุยืน','https://images.unsplash.com/photo-1452570053594-1b985d6ea890?auto=format&fit=crop&w=800&q=80',1),
((SELECT id FROM categories WHERE name='Exotic'),'จุดจุด','เลพเพิร์ด เก็กโค','female','2026-01-15',2500,'ลายจุดน่ารัก เลี้ยงง่าย ไม่ส่งเสียง','https://images.unsplash.com/photo-1633767319476-d43c16b828c4?auto=format&fit=crop&w=800&q=80',1);

INSERT INTO pet_media (pet_id,type,file,caption)
SELECT p.id,'image',p.image,'รูปล่าสุดของน้อง' FROM pets p
WHERE p.image LIKE 'http%' AND NOT EXISTS (SELECT 1 FROM pet_media m WHERE m.pet_id = p.id);
