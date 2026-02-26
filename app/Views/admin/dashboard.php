<?php $currentYear = date('Y'); ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-0">Dashboard Pemerintah</h2>
        <small class="text-muted">
            <i class="bi bi-calendar3 me-1"></i><?= date('l, d F Y') ?>
            <?php if (!empty($settings['city_name'])): ?>&nbsp;&mdash;&nbsp;<?= esc($settings['city_name']) ?><?php endif; ?>
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-people me-1"></i>Kelola User
        </a>
        <a href="<?= base_url('admin/settings') ?>" class="btn btn-success btn-sm">
            <i class="bi bi-gear me-1"></i>Pengaturan
        </a>
    </div>
</div>

<!-- Period Filter -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-2 px-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="text-muted small fw-semibold"><i class="bi bi-funnel me-1"></i>Filter Periode:</span>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-success period-all-btn" data-year="" data-month="">
                    Semua
                </button>
            </div>
            <div class="col-auto">
                <select id="yearSelect" class="form-select form-select-sm" style="width:auto;">
                    <option value="">&ndash; Tahun &ndash;</option>
                    <?php foreach ($availableYears as $yr): ?>
                    <option value="<?= $yr ?>"><?= $yr ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto" id="monthWrap" style="display:none;">
                <select id="monthSelect" class="form-select form-select-sm" style="width:auto;">
                    <option value="">&ndash; Semua Bulan &ndash;</option>
                    <?php $months=['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                    foreach ($months as $num => $name): ?>
                    <option value="<?= $num ?>"><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto" id="filterSpinner" style="display:none;">
                <span class="spinner-border spinner-border-sm text-success"></span>
            </div>
            <div class="col-auto ms-auto">
                <span id="periodLabel" class="badge bg-success bg-opacity-10 text-success fw-normal px-3 py-2 rounded-pill">
                    <i class="bi bi-calendar-check me-1"></i>Semua Waktu
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php
    $cardDefs = [
        ['key'=>'total',      'label'=>'Total Laporan',   'icon'=>'bi-file-earmark-text','bg'=>'#eff6ff','ic'=>'#2563eb','src'=>'report'],
        ['key'=>'pending',    'label'=>'Menunggu',        'icon'=>'bi-hourglass-split',  'bg'=>'#fefce8','ic'=>'#ca8a04','src'=>'report'],
        ['key'=>'in_progress','label'=>'Dalam Proses',    'icon'=>'bi-arrow-repeat',     'bg'=>'#ecfeff','ic'=>'#0891b2','src'=>'report'],
        ['key'=>'cleaned',    'label'=>'Selesai Bersih',  'icon'=>'bi-check-circle',     'bg'=>'#f0fdf4','ic'=>'#16a34a','src'=>'report'],
        ['key'=>'total',      'label'=>'Total User',      'icon'=>'bi-people',           'bg'=>'#f5f3ff','ic'=>'#7c3aed','src'=>'user'],
        ['key'=>'inactive',   'label'=>'User Nonaktif',   'icon'=>'bi-person-x',         'bg'=>'#fff1f2','ic'=>'#e11d48','src'=>'user'],
        ['key'=>'dinas',      'label'=>'Petugas Dinas',   'icon'=>'bi-person-badge',     'bg'=>'#f0f9ff','ic'=>'#0369a1','src'=>'user'],
        ['key'=>'hotspots',   'label'=>'Hotspot Berulang','icon'=>'bi-fire',             'bg'=>'#fff7ed','ic'=>'#ea580c','src'=>'report'],
    ];
    foreach ($cardDefs as $c):
        $val = $c['src']==='user' ? ($userStats[$c['key']] ?? 0) : ($reportStats[$c['key']] ?? 0);
    ?>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:<?= $c['bg'] ?>;">
                    <i class="bi <?= $c['icon'] ?> fs-4" style="color:<?= $c['ic'] ?>;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1 stat-number" style="color:<?= $c['ic'] ?>"
                         data-src="<?= $c['src'] ?>" data-key="<?= $c['key'] ?>"><?= number_format($val) ?></div>
                    <div class="text-muted small mt-1"><?= $c['label'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart text-success"></i> Status Laporan
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="statusChart" style="max-height:260px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart text-primary"></i> User per Peran
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="userChart" style="max-height:260px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="card">
    <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-clock-history text-warning"></i> <span id="recentTitle">Laporan Terbaru</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Pelapor</th><th>Status</th><th>Koordinat</th><th>Waktu</th></tr>
                </thead>
                <tbody id="recentTbody">
                <?php if (empty($recentReports)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada laporan.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentReports as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= esc($r['reporter_name'] ?? '&ndash;') ?></td>
                        <td><span class="badge badge-<?= $r['status'] ?> rounded-pill px-2 py-1"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span></td>
                        <td><small class="text-muted"><?= $r['latitude'] ?>, <?= $r['longitude'] ?></small></td>
                        <td><small><?= date('d M Y H:i', strtotime($r['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
const initialReport={pending:<?= $reportStats['pending'] ?>,reviewed:<?= $reportStats['reviewed'] ?>,in_progress:<?= $reportStats['in_progress'] ?>,cleaned:<?= $reportStats['cleaned'] ?>,rejected:<?= $reportStats['rejected'] ?>};
const initialUser={admin:<?= $userStats['admin'] ?>,dinas:<?= $userStats['dinas'] ?>,masyarakat:<?= $userStats['masyarakat'] ?>};

const statusChart=new Chart(document.getElementById('statusChart'),{type:'doughnut',data:{labels:['Pending','Reviewed','In Progress','Cleaned','Rejected'],datasets:[{data:[initialReport.pending,initialReport.reviewed,initialReport.in_progress,initialReport.cleaned,initialReport.rejected],backgroundColor:['#ffc107','#0dcaf0','#0d6efd','#198754','#dc3545']}]},options:{plugins:{legend:{position:'bottom'}},cutout:'65%'}});

const userChart=new Chart(document.getElementById('userChart'),{type:'bar',data:{labels:['Pemerintah','Dinas','Masyarakat'],datasets:[{label:'Jumlah User',data:[initialUser.admin,initialUser.dinas,initialUser.masyarakat],backgroundColor:['#1a2e40','#198754','#0d6efd'],borderRadius:6}]},options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});

const yearSel=document.getElementById('yearSelect');
const monthSel=document.getElementById('monthSelect');
const monthWrap=document.getElementById('monthWrap');
const spinner=document.getElementById('filterSpinner');
const periodLabel=document.getElementById('periodLabel');
const monthNames=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
let debounceTimer=null;

function fetchStats(){
    const year=yearSel.value, month=monthSel.value;
    spinner.style.display='';
    fetch(`<?= base_url('admin/api/dashboard-stats') ?>?year=${encodeURIComponent(year)}&month=${encodeURIComponent(month)}`,{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(data=>{
        if(data.status!=='success') return;
        const rs=data.reportStats, us=data.userStats;
        document.querySelectorAll('.stat-number').forEach(el=>{
            const val=el.dataset.src==='user'?(us[el.dataset.key]??0):(rs[el.dataset.key]??0);
            el.textContent=Number(val).toLocaleString('id-ID');
        });
        statusChart.data.datasets[0].data=[rs.pending,rs.reviewed,rs.in_progress,rs.cleaned,rs.rejected];
        statusChart.update();
        userChart.data.datasets[0].data=[us.admin,us.dinas,us.masyarakat];
        userChart.update();
        if(!year) periodLabel.innerHTML='<i class="bi bi-calendar-check me-1"></i>Semua Waktu';
        else if(!month) periodLabel.innerHTML=`<i class="bi bi-calendar-check me-1"></i>Tahun ${year}`;
        else periodLabel.innerHTML=`<i class="bi bi-calendar-check me-1"></i>${monthNames[parseInt(month)]} ${year}`;
        const tbody=document.getElementById('recentTbody');
        if(!data.recentReports.length){
            tbody.innerHTML='<tr><td colspan="5" class="text-center text-muted py-4">Belum ada laporan pada periode ini.</td></tr>';
        }else{
            tbody.innerHTML=data.recentReports.map(r=>`<tr><td>${r.id}</td><td>${escHtml(r.reporter_name)}</td><td><span class="badge badge-${r.status} rounded-pill px-2 py-1">${r.status.replace(/_/g,' ')}</span></td><td><small class="text-muted">${r.latitude??''}, ${r.longitude??''}</small></td><td><small>${r.created_at}</small></td></tr>`).join('');
        }
    }).catch(()=>{}).finally(()=>spinner.style.display='none');
}

function escHtml(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}

yearSel.addEventListener('change',()=>{
    monthWrap.style.display=yearSel.value?'':'none';
    if(!yearSel.value)monthSel.value='';
    clearTimeout(debounceTimer);debounceTimer=setTimeout(fetchStats,300);
});
monthSel.addEventListener('change',()=>{clearTimeout(debounceTimer);debounceTimer=setTimeout(fetchStats,300);});
document.querySelector('.period-all-btn').addEventListener('click',()=>{yearSel.value='';monthSel.value='';monthWrap.style.display='none';fetchStats();});
</script>
<?php $extraScripts = ob_get_clean(); ?>