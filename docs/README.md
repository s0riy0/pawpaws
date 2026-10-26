# PawPaws – ระบบ E-Commerce ขายสัตว์เลี้ยง (PHP + MySQL)

## ติดตั้ง (XAMPP)
1. วางโฟลเดอร์ `pawpaws` ไว้ใน `C:\xampp\htdocs\`
2. เปิด Apache + MySQL
3. phpMyAdmin → Import → `database.sql` และ Import → `add_pets_20.sql`
4. เปิด http://localhost/pawpaws

ถ้า MySQL มีรหัสผ่าน แก้ `$DB_USER` / `$DB_PASS` ใน `config.php`

## บัญชีทดสอบ
| Role | Username | Password |
|---|---|---|
| Admin | soriya-admin | admin123 |
| ลูกค้า | Nutchapol | 123456 |

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


## Deploy บน Render.com
Render ไม่มี MySQL ให้ในตัว จึงต้องใช้ MySQL ภายนอก (เช่น Aiven, TiDB Cloud, Railway)
1. สร้างฐานข้อมูล MySQL ว่างๆ แล้วจดค่า host / port / db name / user / password
2. Push โค้ดขึ้น GitHub → Render → New → Blueprint → เลือก repo (ใช้ `render.yaml`)
3. กรอก env: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` (ตั้ง `DB_SSL=1` ถ้า DB บังคับ SSL; ลบออกถ้าไม่ใช้)
4. เริ่มต้นครั้งแรกระบบจะ import `database/database.sql` และ `add_pets_20.sql` ให้อัตโนมัติถ้า DB ยังว่าง
5. หมายเหตุ: ดิสก์ของ Render ชั่วคราว ไฟล์ใน `uploads/` จะหายเมื่อ redeploy — ใช้ Persistent Disk (แพลนเสียเงิน, mount ที่ `/var/www/html/uploads`) หรือเก็บรูปเป็น URL

## Deploy บน InfinityFree
1. รัน `./infinityfree/build.sh` ได้โฟลเดอร์ `dist-infinityfree/` (`htdocs/` + `import.sql`)
2. InfinityFree → Control Panel → MySQL Databases → สร้าง DB (จะได้ host `sqlXXX.infinityfree.com`, ชื่อ DB/user ที่มี prefix `if0_...`)
3. phpMyAdmin → เลือก DB นั้น → Import `import.sql`
4. แก้ `htdocs/config.local.php` ใส่ host/ชื่อ DB/user/password จริง
5. อัปโหลดทุกอย่างใน `htdocs/` ไปที่ `htdocs/` บนโฮสต์ (File Manager หรือ FTP) แล้วเปิดโดเมนของคุณ
