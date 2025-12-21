<?php
header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Staff API Test</title>
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:20px;background:#f0f2f5;color:#1a1a1b}
    .card{background:#fff;border-radius:8px;padding:20px;box-shadow:0 4px 15px rgba(0,0,0,0.1);max-width:900px;margin:auto}
    table{width:100%;border-collapse:collapse;margin-top:15px}
    th,td{text-align:left;padding:10px;border-bottom:1px solid #eee}
    th{background:#f8f9fa}
    
    pre {
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 15px;
        border-radius: 6px;
        font-size: 13px;
        line-height: 1.5;
        
        max-height: 500px; 
        overflow-y: auto; 
        
        white-space: pre-wrap; 
        word-wrap: break-word;
    }

    .btn{background:#198754;color:#fff;border:none;cursor:pointer;padding:10px 16px;border-radius:6px;font-weight:bold}
    .badge{padding:4px 8px;border-radius:4px;font-size:11px;background:#e9ecef}
  </style>
</head>
<body>
  <div class="card">
    <h3>Staff API Test</h3>
    <p>Verifying <strong>GET /api/staff</strong> endpoint and data structure.</p>

    <button class="btn" id="fetchStaff">Fetch Staff List</button>

    <table id="staffTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Position</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="staffBody">
        <tr><td colspan="5" style="text-align:center">Click button to load data...</td></tr>
      </tbody>
    </table>

    <div style="margin-top:20px">
      <h4>Raw API Response (Debug)</h4>
      <pre id="debug">-</pre>
    </div>
  </div>

<script>
  const staffBody = document.getElementById('staffBody');
  const debug = document.getElementById('debug');
  const btn = document.getElementById('fetchStaff');

  async function loadStaff() {
    staffBody.innerHTML = '<tr><td colspan="5" style="text-align:center">Fetching data...</td></tr>';
    debug.textContent = 'Requesting: GET /api/staff...';

    try {
      const res = await fetch('/api/staff', { 
        headers: { 'Accept': 'application/json' } 
      });
      
      const txt = await res.text();

      if (!res.ok) {
        debug.textContent = `Status: ${res.status}\n\n${txt}`;
        staffBody.innerHTML = `<tr><td colspan="5" style="color:red;text-align:center">Error: ${res.status} (Check Authentication)</td></tr>`;
        return;
      }

      const json = JSON.parse(txt);
      
      const prettyJson = JSON.stringify(json, null, 4);
      debug.textContent = `HTTP ${res.status}\nTimestamp: ${json.timestamp || 'N/A'}\n\n${prettyJson}`;

      const staffList = Array.isArray(json.data) ? json.data : [];

      if (staffList.length === 0) {
        staffBody.innerHTML = '<tr><td colspan="5" style="text-align:center">No staff records found.</td></tr>';
        return;
      }

      staffBody.innerHTML = '';
      staffList.forEach(s => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${s.staff_id}</td>
          <td><strong>${s.full_name}</strong></td>
          <td>${s.email}</td>
          <td><span class="badge">${s.position || 'N/A'}</span></td>
          <td>${s.user && s.user.status ? s.user.status : 'N/A'}</td>
        `;
        staffBody.appendChild(row);
      });

    } catch (err) {
      debug.textContent = 'Fetch Error: ' + err.message;
      staffBody.innerHTML = '<tr><td colspan="5" style="color:red;text-align:center">Network Error</td></tr>';
    }
  }

  btn.addEventListener('click', loadStaff);
</script>
</body>
</html>