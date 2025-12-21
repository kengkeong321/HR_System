<?php
header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Positions API Test</title>
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:20px;background:#f7f9fc;color:#111}
    .card{background:#fff;border-radius:8px;padding:18px;box-shadow:0 2px 10px rgba(13,30,60,0.06);max-width:800px;margin:auto}
    select,button{padding:8px 10px;border-radius:6px;border:1px solid #d6dbe8}
    pre{background:#0b1220;color:#e6eef6;padding:12px;border-radius:6px;height:180px;overflow:auto}
    .btn{background:#0d6efd;color:#fff;border:none;cursor:pointer;padding:8px 12px;border-radius:6px}
  </style>
</head>
<body>
  <div class="card">
    <h3>Positions API Test</h3>
    <p class="muted">This page fetches active positions and populates a dropdown list.</p>

    <div style="margin-top:12px">
      <label><strong>Positions</strong></label>
      <select id="positions" style="min-width:360px;padding:8px"><option>Loading…</option></select>
    </div>

    <div style="margin-top:12px">
      <button class="btn" id="reload">Reload</button>
    </div>

    <div style="margin-top:12px">
      <h4>Debug</h4>
      <pre id="debug">-</pre>
    </div>
  </div>

<script>
  const sel = document.getElementById('positions');
  const debug = document.getElementById('debug');
  const reload = document.getElementById('reload');

  async function load(){
    sel.innerHTML = '<option>Loading…</option>';
    debug.textContent = 'GET /api/positions';
    try {
      const res = await fetch('/api/positions', { headers: { 'Accept': 'application/json' } });
      const txt = await res.text();
      debug.textContent = `HTTP ${res.status}\n${txt}`;
      if (!res.ok) return;
      const json = JSON.parse(txt);
      const list = Array.isArray(json.data) ? json.data : [];

      if (json.timestamp) {
        debug.textContent = `HTTP ${res.status}\nTimestamp: ${json.timestamp}\n${txt}`;
      }
      if (!list.length) {
        sel.innerHTML = '<option>No active positions</option>';
        return;
      }
      sel.innerHTML = '<option value="">-- select position --</option>';
      for (const p of list) {
        const opt = document.createElement('option');
        opt.value = p.position_id;
        opt.textContent = p.name + (p.position_id ? ` (${p.position_id})` : '');
        sel.appendChild(opt);
      }
    } catch(err) {
      debug.textContent = err.message;
      sel.innerHTML = '<option>Error</option>';
    }
  }

  reload.addEventListener('click', (e)=>{ e.preventDefault(); load(); });
  document.addEventListener('DOMContentLoaded', load);
</script>
</body>
</html>