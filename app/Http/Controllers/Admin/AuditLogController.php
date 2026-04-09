<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        return view('admin.audit_logs.index', [
            'logs' => AuditLog::orderByDesc('created_at')->limit(200)->get(),
        ]);
    }
}
