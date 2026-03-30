<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SecureHeadersFilter implements FilterInterface
{
    /**
     * @var array<string, string>
     */
    protected array $headers = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-Download-Options' => 'noopen',
        'X-Permitted-Cross-Domain-Policies' => 'none',
        // Allow origin referrer on cross-site tile requests (e.g. OSM).
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ];

    /**
     * @param list<string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    /**
     * @param list<string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        foreach ($this->headers as $header => $value) {
            $response->setHeader($header, $value);
        }

        return $response;
    }
}
