<?php
require_once __DIR__ . '/functions.php';
requireLogin();
$pageTitle = 'Dashboard';
$user = getCurrentUser();
$role = $user['role'];
include __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div>
    <h2><i class="ri-dashboard-line me-2"></i>Dashboard</h2>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Dashboard</li></ol></nav>
  </div>
  <?php if (in_array($role, ['admin','manager'])): ?>
  <button class="btn btn-primary btn-fetch" id="fetchBtn" onclick="triggerFetch()">
    <i class="ri-refresh-line me-1"></i> Fetch OTPs Now
  </button>
  <?php endif; ?>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4" id="statsCards">
  <div class="col-6 col-xl-2">
    <div class="stat-card bg-stat-1">
      <div><div class="stat-val" id="s-today-sms">–</div><div class="stat-label">Today SMS</div></div>
      <div class="stat-icon"><i class="ri-message-2-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="stat-card bg-stat-2">
      <div><div class="stat-val" id="s-week-sms">–</div><div class="stat-label">Week SMS</div></div>
      <div class="stat-icon"><i class="ri-message-3-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="stat-card bg-stat-3">
      <div><div class="stat-val" id="s-today-profit">–</div><div class="stat-label">Today Profit</div></div>
      <div class="stat-icon"><i class="ri-money-dollar-circle-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="stat-card bg-stat-4">
      <div><div class="stat-val" id="s-week-profit">–</div><div class="stat-label">Week Profit</div></div>
      <div class="stat-icon"><i class="ri-funds-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="stat-card bg-stat-5">
      <div><div class="stat-val" id="s-total-numbers">–</div><div class="stat-label">Numbers</div></div>
      <div class="stat-icon"><i class="ri-sim-card-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2">
    <div class="stat-card bg-stat-6">
      <div><div class="stat-val" id="s-total-users">–</div><div class="stat-label"><?= ($role === 'reseller') ? 'Clients' : 'Users' ?></div></div>
      <div class="stat-icon"><i class="ri-team-line"></i></div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>SMS Activity — Last 7 Days</span>
        <span class="badge bg-primary">Line Chart</span>
      </div>
      <div class="card-body">
        <div id="chartSms" style="min-height:250px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">Top 5 Services</div>
      <div class="card-body">
        <div id="chartServices" style="min-height:250px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Recent OTPs -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="ri-time-line me-1"></i> Recent OTPs</span>
    <a href="<?= APP_URL ?>/sms_reports.php" class="btn btn-sm btn-outline-primary">View All</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" id="recentOtpsTable">
        <thead>
          <tr>
            <th>Received At</th>
            <th>Number</th>
            <th>Service</th>
            <th>Country</th>
            <th>OTP</th>
            <th>Message</th>
            <?php if (in_array($role, ['admin','manager','reseller'])): ?>
            <th>Profit</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="recentOtpsBody">
          <tr><td colspan="7" class="text-center text-muted py-4"><i class="ri-loader-4-line me-1"></i>Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- News Section (for resellers) -->
<?php if (in_array($role, ['reseller','sub_reseller'])): ?>
<div class="card mt-3">
  <div class="card-header"><i class="ri-megaphone-line me-1"></i>Announcements</div>
  <div class="card-body" id="newsSection">
    <?php
    $pdo = getDB();
    $news = $pdo->query("SELECT n.*, u.username FROM news_master n JOIN users u ON n.created_by = u.id ORDER BY n.created_at DESC LIMIT 5")->fetchAll();
    if (empty($news)): ?>
      <p class="text-muted mb-0">No announcements.</p>
    <?php else: foreach ($news as $item): ?>
      <div class="mb-3 pb-3 border-bottom">
        <h6 class="mb-1"><?= h($item['title']) ?></h6>
        <p class="mb-1 text-muted" style="font-size:.875rem;"><?= nl2br(h($item['content'])) ?></p>
        <small class="text-muted">By <?= h($item['username']) ?> — <?= h($item['created_at']) ?></small>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<?php endif; ?>

<?php
$extraJs = <<<JS
<meta name="csrf" content="{$_SESSION['csrf_token']}">
<script>
// Load stats
$.getJSON('<?= APP_URL ?>/ajax/dashboard_stats.php', function(d) {
    if (d.status === 'success') {
        var s = d.data;
        $('#s-today-sms').text(s.today_sms.toLocaleString());
        $('#s-week-sms').text(s.week_sms.toLocaleString());
        $('#s-today-profit').text('$' + parseFloat(s.today_profit).toFixed(4));
        $('#s-week-profit').text('$' + parseFloat(s.week_profit).toFixed(4));
        $('#s-total-numbers').text(s.total_numbers.toLocaleString());
        $('#s-total-users').text(s.total_users.toLocaleString());
    }
});

// Load charts
$.getJSON('<?= APP_URL ?>/ajax/dashboard_charts.php?type=sms', function(d) {
    if (!d.categories) return;
    var options = {
        chart: { type: 'area', height: 250, toolbar: { show: false }, sparkline: { enabled: false } },
        series: [{ name: 'SMS', data: d.data }],
        xaxis: { categories: d.categories, labels: { style: { fontSize: '11px' } } },
        colors: ['#1e3a5f'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
        stroke: { curve: 'smooth', width: 2 },
        markers: { size: 4 },
        tooltip: { y: { formatter: v => v + ' SMS' } },
        grid: { borderColor: '#f1f5f9' },
        dataLabels: { enabled: false },
    };
    new ApexCharts(document.querySelector('#chartSms'), options).render();
});

$.getJSON('<?= APP_URL ?>/ajax/dashboard_charts.php?type=services', function(d) {
    if (!d.labels) return;
    var options = {
        chart: { type: 'donut', height: 250 },
        series: d.data,
        labels: d.labels,
        colors: ['#1e3a5f','#0d6efd','#198754','#fd7e14','#6f42c1'],
        legend: { position: 'bottom', fontSize: '12px' },
        dataLabels: { enabled: true, formatter: (v) => v.toFixed(1) + '%' },
        tooltip: { y: { formatter: v => v + ' SMS' } },
    };
    new ApexCharts(document.querySelector('#chartServices'), options).render();
});

// Load recent OTPs
$.getJSON('<?= APP_URL ?>/ajax/dashboard_stats.php?recent=1', function(d) {
    var tbody = $('#recentOtpsBody');
    if (!d.recent || d.recent.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">No OTPs received yet.</td></tr>');
        return;
    }
    var html = '';
    d.recent.forEach(function(r) {
        html += '<tr>';
        html += '<td><small>' + r.received_at + '</small></td>';
        html += '<td><code>' + r.number + '</code></td>';
        html += '<td><span class="badge bg-info text-dark">' + (r.service || '–') + '</span></td>';
        html += '<td>' + (r.country || '–') + '</td>';
        html += '<td><strong>' + r.otp + '</strong></td>';
        html += '<td><small class="text-muted">' + (r.message ? r.message.substring(0,60) + (r.message.length>60?'…':'') : '–') + '</small></td>';
        <?php if (in_array($role, ['admin','manager','reseller'])): ?>
        html += '<td>' + (r.profit ? '<span class="text-success">$' + parseFloat(r.profit).toFixed(6) + '</span>' : '<span class="text-muted">–</span>') + '</td>';
        <?php endif; ?>
        html += '</tr>';
    });
    tbody.html(html);
});

// Fetch OTPs trigger
function triggerFetch() {
    var btn = document.getElementById('fetchBtn');
    if (!btn) return;
    btn.classList.add('loading');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Fetching…';
    $.getJSON('<?= APP_URL ?>/ajax/cron_fetch.php', function(d) {
        btn.classList.remove('loading');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-refresh-line me-1"></i> Fetch OTPs Now';
        if (d.status === 'success') {
            showToast('✅ Fetched! New SMS: ' + d.new_count, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(d.message || 'Fetch failed', 'warning');
        }
    }).fail(function() {
        btn.classList.remove('loading');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-refresh-line me-1"></i> Fetch OTPs Now';
        showToast('Fetch request failed', 'danger');
    });
}
</script>
JS;
include __DIR__ . '/includes/footer.php';
?>
