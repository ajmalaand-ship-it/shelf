<?php
namespace App\Http\Middleware;
class RecoveryBlocked
{
    public function handle($request, \Closure $next)
    {
        abort_if(is_file(base_path('.shelf-recovery-blocked')), 503, 'Recovery review is required before reopening Shelf.');
        return $next($request);
    }
}
