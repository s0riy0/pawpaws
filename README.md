# PawPaws – ระบบ E-Commerce ขายสัตว์เลี้ยง (PHP + MySQL)

## ติดตั้ง (XAMPP)
1. วางโฟลเดอร์ `pawpaws` ไว้ใน `C:\xampp\htdocs\`
2. เปิด Apache + MySQL
3. phpMyAdmin → Import → `database.sql`
4. เปิด http://localhost/pawpaws

ถ้า MySQL มีรหัสผ่าน แก้ `$DB_USER` / `$DB_PASS` ใน `config.php`

## บัญชีทดสอบ
| Role | Username | Password |
|---|---|---|
| Admin | admin | admin123 |
| ลูกค้า | customer | 123456 |

## หน้าเว็บ
| หน้า | ไฟล์ |
|---|---|
| หน้าหลัก | index.php |
| Login / สมัครสมาชิก (18+) | login.php / register.php |
| เพิ่มข้อมูล (แอดมิน) | pet_add.php |
| ค้นหาข้อมูล | search.php |
| แก้ไขข้อมูล + อัปเดตรูป/วิดีโอ (แอดมิน) | pet_edit.php |
| ลบข้อมูล (แอดมิน) | pet_delete.php |
| รายงาน 4 รายงาน (แอดมิน) | reports.php |
| โปรไฟล์สัตว์เลี้ยง | pet.php |
| ตะกร้า / ชำระเงิน | cart.php / checkout.php |
| ติดตามการจัดส่ง (อัปเดตทุก 5 วิ) | orders.php / track.php / track_api.php |
| แชทลูกค้า ↔ แอดมิน | chat.php / chat_api.php |
| แดชบอร์ดแอดมิน | admin.php |

## รูปตัวอย่าง
รูปน้องๆ ในข้อมูลตัวอย่างโหลดจาก Unsplash (ต้องต่ออินเทอร์เน็ต) ถ้าจะใช้แบบออฟไลน์ ให้ล็อกอินแอดมิน → ปุ่ม **บันทึกรูปตัวอย่างลงเครื่อง** (`fetch_images.php`) ระบบจะดาวน์โหลดเก็บไว้ใน `uploads/`

ภาพจาก Unsplash (Unsplash License – ใช้ฟรี): Ben Michel (บัดดี้), Ion Sipilov (มะลิ), Thomas Haas (ส้มโอ), Leiada Krözjhen (โมจิ), Sandy Millar (ปุยฝ้าย), Pablo Franco (ลาวา), Richard Brutyo (ลาเต้), Joe Caione (ข้าวปั้น), PartTime Portraits (น้ำผึ้ง), Bill Stephan (มาร์ช), Peri Stojnic (หิมะ), charlesdeluvio (ถั่วดำ), Alvan Nee (ขนมปัง), Amber Kipp (ส้มจี๊ด), Loan (ซิลเวอร์), hang niu (ปุยนุ่น), Raoul Droog (ฟ้าใส), Andriyko Podilnyk (แครอท), Waranya Mooldee (ช็อกโก), Satyabratasm (สโนว์), Ricky Kharawala (ถั่วงอก), Jack Catalano (โมโม่), Gustavo Zambelli (คุกกี้), Zdeněk Macháček (กีวี่), Andrew Pons (มะม่วง), Andrey Tikhonovskiy (จุดจุด)

เพิ่มน้อง 20 ตัวในฐานข้อมูลที่มีอยู่แล้ว: import `add_pets_20.sql`

ER Diagram + Mapping + Data Dictionary อยู่ใน `docs/`
