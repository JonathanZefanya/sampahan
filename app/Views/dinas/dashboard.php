<?php
$cards = [
    ['key'=>'total',      'label'=>'Total Laporan','icon'=>'bi-file-earmark-text','bg'=>'#eff6ff','ic'=>'#2563eb'],
    ['key'=>'pending',    'label'=>'Pending',      'icon'=>'bi-hourglass-split',  'bg'=>'#fefce8','ic'=>'#ca8a04'],
    ['key'=>'in_progress','label'=>'Dalam Proses', 'icon'=>'bi-arrow-repeat',     'bg'=>'#ecfeff','ic'=>'#0891b2'],
    ['key'=>'cleaned',    'label'=>'Selesai',      'icon'=>'bi-check-circle',     'bg'=>'#f0fdf4','ic'=>'#16a34a'],
];
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
    <div class="me-auto">
        <h2 class="fw-bold mb-0"><i class="bi bi-speedometer2 text-success me-2"></i>Dashboard Dinas</h2>
        <small class="text-muted">
            <i class="bi bi-calendar3 me-1"></i><?= date('l, d F Y') ?>
            <?php if (!empty($settings['city_name'])): ?>&nbsp;&mdash;&nbsp;<?= esc($settings['city_name']) ?><?php endif; ?>
        </small>
    </div>
    <a href="<?= base_url('dinas/map') ?>" class="btn btn-success">
        <i class="bi bi-map me-1"></i> Buka Peta Laporan
    </a>
</div>

<!-- Period Filter -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-2 px-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="text-muted small fw-semibold"><i class="bi bi-funnel me-1"></i>Filter Periode:</span>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-success period-all-btn">Semua</button>
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
    <?php foreach ($cards as $c): ?>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:<?= $c['bg'] ?>">
                    <i class="bi <?= $c['icon'] ?> fs-4" style="color:<?= $c['ic'] ?>"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1 stat-number" style="color:<?= $c['ic'] ?>"
                         data-key="<?= $c['key'] ?>"><?= number_format($stats[$c['key']] ?? 0) ?></div>
                    <div class="text-muted small mt-1"><?= $c['label'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Recent Reports Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
        <span class="fw-semibold"><i class="bi bi-list-ul text-success me-2"></i>Laporan Aktif Terbaru</span>
        <a href="<?= base_url('dinas/map') ?>" class="btn btn-sm btn-success">
            <i class="bi bi-map me-1"></i> Peta Lengkap
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Pelapor</th>
                        <th>Status</th>
                        <th class="pe-3">Waktu</th>
                    </tr>
                </thead>
                <tbody id="recentTbody">
                <?php if (empty($recentReports)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                        Tidak ada laporan aktif.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($recentReports as $r): ?>
                    <tr>
                        <td class="ps-3 text-muted">#<?= $r['id'] ?></td>
                        <td>
                            <?php $nm=$r['reporter_name']??''; $colors=['#2563eb','#16a34a','#ca8a04','#0891b2','#dc2626','#7c3aed']; $bg=$colors[ord($nm[0]??'A')%count($colors)]; ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                     style="width:32px;height:32px;background:<?= $bg ?>;font-size:12px;"><?= esc(mb_strtoupper(mb_substr($nm,0,1))) ?></div>
                                <?= esc($nm) ?>
                            </div>
                        </td>
                        <td><span class="badge badge-<?= $r['status'] ?> rounded-pill px-2"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span></td>
                        <td class="pe-3"><small class="text-muted"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></small></td>
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
const monthNames=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const yearSel=document.getElementById('yearSelect');
const monthSel=document.getElementById('monthSelect');
const monthWrap=document.getElementById('monthWrap');
const spinner=document.getElementById('filterSpinner');
const periodLabel=document.getElementById('periodLabel');
let debounceTimer=null;

function fetchStats(){
    const year=yearSel.value, month=monthSel.value;
    spinner.style.display='';
    fetch(`<?= base_url('dinas/api/dashboard-stats') ?>?year=${encodeURIComponent(year)}&month=${encodeURIComponent(month)}`,{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(data=>{
        if(data.status!=='success') return;
        const rs=data.stats;
        document.querySelectorAll('.stat-number').forEach(el=>{
            el.textContent=Number(rs[el.dataset.key]??0).toLocaleString('id-ID');
        });
        if(!year) periodLabel.innerHTML='<i class="bi bi-calendar-check me-1"></i>Semua Waktu';
        else if(!month) periodLabel.innerHTML=`<i class="bi bi-calendar-check me-1"></i>Tahun ${year}`;
        else periodLabel.innerHTML=`<i class="bi bi-calendar-check me-1"></i>${monthNames[parseInt(month)]} ${year}`;
        const tbody=document.getElementById('recentTbody');
        if(!data.recentReports.length){
            tbody.innerHTML='<tr><td colspan="4" class="text-center text-muted py-5"><i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>Tidak ada laporan aktif.</td></tr>';
        }else{
            tbody.innerHTML=data.recentReports.map(r=>{
                const nm=r.reporter_name||'';
                const colors=['#2563eb','#16a34a','#ca8a04','#0891b2','#dc2626','#7c3aed'];
                const bg=colors[nm.charCodeAt(0)%colors.length]||colors[0];
                return `<tr>
                    <td class="ps-3 text-muted">#${r.id}</td>
                    <td><div class="d-flex align-items-center gap-2"><div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:32px;height:32px;background:${bg};font-size:12px;">${escHtml(nm.charAt(0).toUpperCase())}</div>${escHtml(nm)}</div></td>
                    <td><span class="badge badge-${r.status} rounded-pill px-2">${r.status.replace(/_/g,' ')}</span></td>
                    <td class="pe-3"><small class="text-muted">${r.created_at}</small></td>
                </tr>`;
            }).join('');
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