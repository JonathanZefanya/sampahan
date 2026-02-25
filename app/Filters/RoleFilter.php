<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter
 *
 * Usage in Routes.php:
 *   $routes->group('admin', ['filter' => 'role:admin'], function ($routes) { ... });
 *   $routes->group('dinas', ['filter' => 'role:dinas,admin'], function ($routes) { ... });
 *
 * Multiple allowed roles can be comma-separated in the filter argument.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $session  = session();
        $user     = $session->get('user');

        if (! $user) {
            return redirect()->to('/auth/login');
        }

        $allowedRoles = $arguments ?? [];

        if (! empty($allowedRoles) && ! in_array($user['role'], $allowedRoles, true)) {
            return $this->redirectToDashboard($user['role']);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }

    private function redirectToDashboard(string $role): \CodeIgniter\HTTP\RedirectResponse
    {
        $routes = [
            'admin'       => '/admin/dashboard',
            'dinas'       => '/dinas/dashboard',
            'masyarakat'  => '/masyarakat/dashboard',
        ];

        return redirect()->to($routes[$role] ?? '/')->with('error', 'Akses ditolak.');
    }
}
