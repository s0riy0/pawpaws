-- เพิ่มน้อง 20 ตัว (สำหรับคนที่ import database.sql ไปแล้ว)
SET NAMES utf8mb4;
USE pawpaws;

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
