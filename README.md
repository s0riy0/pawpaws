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


