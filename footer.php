</main>
<footer class="foot">
  <div class="wrap foot-in">
    <div><strong>🐾 PawPaws</strong><p class="muted">ส่งมอบน้องๆ สู่บ้านใหม่ที่อบอุ่น</p></div>
    <p class="muted">© <?= date('Y') + 543 ?> PawPaws</p>
  </div>
</footer>
<?php if (user()): ?>
<script>
(() => {
  let ctx, lastBeep = 0;
  const unlock = () => { try { ctx = ctx || new (window.AudioContext || window.webkitAudioContext)(); ctx.resume(); } catch (e) {} };
  ['click', 'keydown', 'touchstart'].forEach(ev => document.addEventListener(ev, unlock, {once: true}));
  window.chatBeep = () => {
    const now = Date.now();
    if (now - lastBeep < 1500) return;
    lastBeep = now;
    try {
      unlock();
      [[880, 0], [1320, 0.15]].forEach(([f, t]) => {
        const o = ctx.createOscillator(), g = ctx.createGain(), s = ctx.currentTime + t;
        o.type = 'sine'; o.frequency.value = f;
        g.gain.setValueAtTime(0.0001, s);
        g.gain.exponentialRampToValueAtTime(0.3, s + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, s + 0.25);
        o.connect(g).connect(ctx.destination); o.start(s); o.stop(s + 0.3);
      });
    } catch (e) {}
  };

  const dot = document.getElementById('chatDot'), threads = document.getElementById('threads');
  const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const baseTitle = document.title;
  let seen = null;

  async function poll() {
    try {
      const r = await (await fetch('chat_api.php?status=1', {cache: 'no-store'})).json();
      if (dot) { dot.textContent = r.total > 99 ? '99+' : (r.total || ''); dot.classList.toggle('on', r.total > 0); }
      document.title = r.total > 0 ? `(${r.total}) ${baseTitle}` : baseTitle;
      if (seen !== null && r.last > seen && r.total > 0) window.chatBeep();
      seen = r.last;
      if (threads && r.threads) {
        const cur = +threads.dataset.cur;
        threads.innerHTML = r.threads.length ? r.threads.map(t => {
          const un = t.unread && t.id !== cur;
          return `<a href="chat.php?c=${t.id}" class="${t.id === cur ? 'on' : ''}${un ? ' unread' : ''}"><span class="cdot"></span><strong>${esc(t.name)}</strong>${un ? `<span class="cnt">${t.unread}</span>` : ''}<small>${esc(t.body)}</small></a>`;
        }).join('') : '<p class="muted small" style="padding:16px">ยังไม่มีข้อความจากลูกค้า</p>';
      }
    } catch (e) {}
  }
  poll();
  setInterval(poll, 4000);
})();
</script>
<?php endif; ?>
</body>
</html>
