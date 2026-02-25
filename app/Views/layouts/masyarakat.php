<?php
// Masyarakat layout
$sidebarNav = '
<a href="' . base_url('masyarakat/dashboard') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'dashboard' ? 'active' : '') . '">
    <i class="bi bi-house"></i> Beranda
</a>
<a href="' . base_url('masyarakat/report') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'report' ? 'active' : '') . '">
    <i class="bi bi-plus-circle"></i> Laporkan Sampah
</a>
<a href="' . base_url('masyarakat/history') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'history' ? 'active' : '') . '">
    <i class="bi bi-clock-history"></i> Riwayat Laporan
</a>
<a href="' . base_url('masyarakat/profile') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'profile' ? 'active' : '') . '">
    <i class="bi bi-person-circle"></i> Profil Saya
</a>
';

echo view('layouts/_base', array_merge(get_defined_vars(), ['sidebarNav' => $sidebarNav]));
