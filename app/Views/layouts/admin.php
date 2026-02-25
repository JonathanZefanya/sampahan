<?php
// Admin layout – injects admin-specific sidebar nav, then delegates to the base layout.
$sidebarNav = '
<a href="' . base_url('admin/dashboard') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'dashboard' ? 'active' : '') . '">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="' . base_url('admin/users') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'users' ? 'active' : '') . '">
    <i class="bi bi-people"></i> Manajemen User
</a>
<a href="' . base_url('admin/reports') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'reports' ? 'active' : '') . '">
    <i class="bi bi-clipboard2-data"></i> Manajemen Laporan
</a>
<a href="' . base_url('admin/settings') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'settings' ? 'active' : '') . '">
    <i class="bi bi-gear"></i> Pengaturan Sistem
</a>
<a href="' . base_url('admin/profile') . '" class="nav-link ' . (service('uri')->getSegment(2) === 'profile' ? 'active' : '') . '">
    <i class="bi bi-person-circle"></i> Profil Saya
</a>
';

// Pass all existing data + sidebar nav to the base layout
echo view('layouts/_base', array_merge(
    get_defined_vars(),
    ['sidebarNav' => $sidebarNav]
));
