<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter
 *
 * Ensures a user is authenticated and their account is active.
 * Applied via $filters alias 'auth' in app/Config/Filters.php.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $session = session();

        if (! $session->has('user')) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = $session->get('user');

        // Hard block for deactivated accounts (could be deactivated while logged in)
        if (empty($user['is_active'])) {
            $session->destroy();
            return redirect()->to('/auth/login')->with('error', 'Akun dinonaktifkan Admin.');
        }

        return null; // continue
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
