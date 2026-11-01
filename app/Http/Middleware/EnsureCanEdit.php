<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureCanEdit {
    public function handle(Request $request, Closure $next): Response {
        abort_unless($request->user() && in_array($request->user()->role,['admin','petugas'],true),403,'Anda tidak memiliki izin mengubah data.');
        return $next($request);
    }
}
