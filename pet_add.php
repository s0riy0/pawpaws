<?php
require 'config.php';
require_admin();

$cats = $pdo->query('SELECT * FROM categories ORDER BY id')->fetchAll();
$v = ['name' => '', 'category_id' => $cats[0]['id'] ?? '', 'breed' => '', 'gender' => 'male', 'birth_date' => '', 'price' => '', 'description' => ''];
$err = [];
$isEdit = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    [$in, $err] = pet_input(array_intersect_key($_POST, $v));
    $v = $in + $v;
    if (!$err) {
        try {
            $img = upload('image', IMG_EXT);
            $vid = upload('video', array_merge(IMG_EXT, VID_EXT));
            $pdo->prepare('INSERT INTO pets (category_id,name,breed,gender,birth_date,price,description,image,created_by) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([(int)$v['category_id'], $v['name'], $v['breed'], $v['gender'], $v['birth_date'], $v['price'], $v['description'], $img, user()['id']]);
            $id = $pdo->lastInsertId();
            if ($vid) {
                $vType = in_array(strtolower(pathinfo($vid, PATHINFO_EXTENSION)), VID_EXT, true) ? 'video' : 'image';
                $pdo->prepare("INSERT INTO pet_media (pet_id,type,file,caption) VALUES (?,?,?,?)")->execute([$id, $vType, $vid, $vType === 'video' ? 'วิดีโอแรกของน้อง' : 'รูปเพิ่มเติม']);
            }
            flash("เพิ่มข้อมูล {$v['name']} เรียบร้อย");
            redirect("pet.php?id=$id");
        } catch (Exception $ex) {
            $err[] = $ex->getMessage();
        }
    }
}

$title = 'เพิ่มน้องใหม่';
include 'header.php';
?>
<div class="wrap page">
  <div class="auth wide box" style="margin-top:0">
    <h1>เพิ่มข้อมูลน้องใหม่</h1>
    <p class="muted">กรอกโปรไฟล์สัตว์เลี้ยงให้ครบถ้วน</p>
    <?php if ($err): ?><div class="alert err"><?= implode('<br>', array_map('e', $err)) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <?php include 'pet_form.php'; ?>
      <div class="row"><button class="btn">บันทึกข้อมูล</button><a class="btn ghost" href="admin.php">ยกเลิก</a></div>
    </form>
  </div>
</div>
<?php include 'footer.php'; ?>
