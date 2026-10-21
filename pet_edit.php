<?php
require 'config.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM pets WHERE id = ?');
$st->execute([$id]);
$pet = $st->fetch();
if (!$pet) { flash('กรุณาเลือกน้องที่ต้องการแก้ไข', 'err'); redirect('admin.php'); }

$cats = $pdo->query('SELECT * FROM categories ORDER BY id')->fetchAll();
$v = $pet;
$err = [];
$isEdit = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? 'save';
    try {
        if ($action === 'save') {
            $fields = ['name', 'category_id', 'breed', 'gender', 'birth_date', 'price', 'description', 'status'];
            [$in, $err] = pet_input(array_intersect_key($_POST, array_flip($fields)));
            $v = $in + $v;
            if (!$err) {
                $img = upload('image', IMG_EXT);
                if ($img) delete_file($pet['image']);
                $pdo->prepare('UPDATE pets SET category_id=?, name=?, breed=?, gender=?, birth_date=?, price=?, description=?, status=?, image=? WHERE id=?')
                    ->execute([(int)$v['category_id'], $v['name'], $v['breed'], $v['gender'], $v['birth_date'], $v['price'], $v['description'], $v['status'], $img ?: $pet['image'], $id]);
                flash('บันทึกการแก้ไขเรียบร้อย');
                redirect("pet_edit.php?id=$id");
            }
        } elseif ($action === 'add_media') {
            $file = upload('media', array_merge(IMG_EXT, VID_EXT));
            if (!$file) throw new Exception('กรุณาเลือกไฟล์รูปภาพหรือวิดีโอ');
            $type = in_array(pathinfo($file, PATHINFO_EXTENSION), VID_EXT, true) ? 'video' : 'image';
            $pdo->prepare('INSERT INTO pet_media (pet_id, type, file, caption) VALUES (?,?,?,?)')->execute([$id, $type, $file, trim($_POST['caption'] ?? '')]);
            flash('อัปเดต' . ($type === 'video' ? 'วิดีโอ' : 'รูปภาพ') . 'ตัวจริงเรียบร้อย');
            redirect("pet_edit.php?id=$id#media");
        } elseif ($action === 'del_media') {
            $m = $pdo->prepare('SELECT file FROM pet_media WHERE id = ? AND pet_id = ?');
            $m->execute([(int)$_POST['media_id'], $id]);
            if ($f = $m->fetchColumn()) {
                $pdo->prepare('DELETE FROM pet_media WHERE id = ?')->execute([(int)$_POST['media_id']]);
                delete_file($f);
                flash('ลบไฟล์เรียบร้อย');
            }
            redirect("pet_edit.php?id=$id#media");
        }
    } catch (Exception $ex) {
        $err[] = $ex->getMessage();
    }
}

$media = $pdo->prepare('SELECT * FROM pet_media WHERE pet_id = ? ORDER BY created_at DESC');
$media->execute([$id]);
$media = $media->fetchAll();

$title = 'แก้ไข ' . $pet['name'];
include 'header.php';
?>
<div class="wrap page">
  <div class="page-title"><h1>แก้ไขข้อมูล: <?= e($pet['name']) ?></h1><a class="btn ghost sm" href="pet.php?id=<?= $id ?>">ดูหน้าโปรไฟล์ →</a></div>
  <?php if ($err): ?><div class="alert err"><?= implode('<br>', array_map('e', $err)) ?></div><?php endif; ?>
  <div class="split">
    <form class="box" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="save">
      <?php include 'pet_form.php'; ?>
      <div class="row"><button class="btn">บันทึกการแก้ไข</button><a class="btn ghost" href="admin.php">กลับ</a></div>
    </form>
    <div>
      <div class="box">
        <h3>รูปหลักปัจจุบัน</h3>
        <div class="thumb" style="border-radius:16px;overflow:hidden"><?= img_tag($pet['image'], $pet['name']) ?></div>
      </div>
      <form class="box mt" method="post" enctype="multipart/form-data" id="media">
        <?= csrf_field() ?><input type="hidden" name="action" value="add_media">
        <h3>อัปเดตรูป/วิดีโอตัวจริง</h3>
        <div class="field"><input type="file" name="media" accept="image/*,video/*" required></div>
        <div class="field"><input name="caption" maxlength="255" placeholder="คำอธิบาย เช่น วันนี้น้องอาบน้ำแล้ว"></div>
        <button class="btn block">อัปโหลด</button>
      </form>
    </div>
  </div>

  <h2 class="mt" style="margin-top:40px">ไฟล์อัปเดตทั้งหมด (<?= count($media) ?>)</h2>
  <div class="media">
    <?php foreach ($media as $m): ?>
      <figure>
        <?php if ($m['type'] === 'video'): ?><video src="<?= e(media_url($m['file'])) ?>" controls preload="metadata"></video>
        <?php else: ?><img src="<?= e(media_url($m['file'])) ?>" alt=""><?php endif; ?>
        <figcaption class="row between"><span><?= e($m['caption'] ?: '-') ?><br><span class="muted"><?= thai_date($m['created_at'], true) ?></span></span>
          <form method="post" onsubmit="return confirm('ลบไฟล์นี้?')"><?= csrf_field() ?><input type="hidden" name="action" value="del_media"><input type="hidden" name="media_id" value="<?= $m['id'] ?>"><button class="btn sm ghost">ลบ</button></form>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</div>
<?php include 'footer.php'; ?>
