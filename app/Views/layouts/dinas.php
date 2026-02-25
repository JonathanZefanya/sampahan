<?php
// Dinas layout
$sidebarNav = '
<a href="' . base_url('dinas/dashboard') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'dashboard' ? 'active' : '') . '">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="' . base_url('dinas/map') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'map' ? 'active' : '') . '">
    <i class="bi bi-map"></i> Peta Laporan
</a>
<a href="' . base_url('dinas/profile') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'profile' ? 'active' : '') . '">
    <i class="bi bi-person-circle"></i> Profil Saya
</a>
';

echo view('layouts/_base', array_merge(get_defined_vars(), ['sidebarNav' => $sidebarNav]));
