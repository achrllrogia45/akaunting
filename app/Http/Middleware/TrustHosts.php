<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * Default was the application URL plus subdomains, which rejects any
     * additional hostname (a Cloudflare tunnel hostname answers 404 otherwise).
     * Additional hosts come from TRUSTED_HOSTS, comma or space separated.
     *
     * @return array
     */
    public function hosts()
    {
        $hosts = [$this->allSubdomainsOfApplicationUrl()];

        foreach (preg_split('/[\s,]+/', (string) env('TRUSTED_HOSTS', '')) as $host) {
            if ($host !== '') {
                $hosts[] = '^(.+\.)?' . preg_quote($host) . '$';
            }
        }

        return array_filter($hosts);
    }
}
