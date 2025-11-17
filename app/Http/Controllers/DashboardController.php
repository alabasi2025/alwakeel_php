<?php

namespace App\Http\Controllers;

use App\Models\Command;
use App\Models\Result;
use App\Models\Integration;
use App\Models\SyncLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $commandsCount = Command::count();
        $resultsCount = Result::count();
        $integrationsCount = Integration::where('is_enabled', 'true')->count();
        $syncLogsCount = SyncLog::count();
        $recentLogs = SyncLog::orderBy('created_at', 'desc')->limit(5)->get();

        return view('dashboard', compact(
            'commandsCount',
            'resultsCount',
            'integrationsCount',
            'syncLogsCount',
            'recentLogs'
        ));
    }
}
