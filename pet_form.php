<?php
$sel = fn($a, $b) => (string)$a === (string)$b ? 'selected' : '';
?>
<div class="grid2">
  <div class="field"><label>ชื่อน้อง *</label><input name="name" required maxlength="100" value="<?= e($v['name']) ?>"></div>
  <div class="field"><label>ประเภท *</label><select name="category_id" required>
    <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $sel($v['category_id'], $c['id']) ?>><?= e($c['name']) ?></option><?php endforeach; ?>
  </select></div>
  <div class="field"><label>สายพันธุ์</label><input name="breed" maxlength="100" value="<?= e($v['breed']) ?>"></div>
  <div class="field"><label>เพศ *</label><select name="gender">
    <option value="male" <?= $sel($v['gender'], 'male') ?>>ตัวผู้</option><option value="female" <?= $sel($v['gender'], 'female') ?>>ตัวเมีย</option>
  </select></div>
  <div class="field"><label>วันเกิด *</label><input type="date" name="birth_date" required max="<?= date('Y-m-d') ?>" value="<?= e($v['birth_date']) ?>"></div>
  <div class="field"><label>ราคา (บาท) *</label><input type="number" name="price" required min="0" step="0.01" value="<?= e($v['price']) ?>"></div>
  <?php if ($isEdit): ?>
  <div class="field"><label>สถานะ</label><select name="status">
    <option value="available" <?= $sel($v['status'], 'available') ?>>พร้อมรับเลี้ยง</option><option value="sold" <?= $sel($v['status'], 'sold') ?>>ได้บ้านแล้ว</option>
  </select></div>
  <?php endif; ?>
  <div class="field"><label>รูปหลัก <?= $isEdit ? '(เลือกเพื่อเปลี่ยน)' : '' ?></label><input type="file" name="image" accept="image/*"></div>
  <?php if (!$isEdit): ?>
  <div class="field"><label>วิดีโอตัวจริง (ถ้ามี)</label><input type="file" name="video" accept="video/*,image/*"></div>
  <?php endif; ?>
</div>
<div class="field"><label>รายละเอียด / นิสัย / ประวัติวัคซีน</label><textarea name="description"><?= e($v['description']) ?></textarea></div>
