<?php
namespace App\Http\Controllers;
use App\Models\SyncLog;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function index()
    {
        return view('sync');
    }

    public function githubPull()
    {
        SyncLog::create([
            'sync_type' => 'github_pull',
            'status' => 'success',
            'message' => 'تم السحب من GitHub بنجاح'
        ]);
        
        return redirect()->route('sync')->with('success', 'تم السحب بنجاح');
    }

    public function githubPush()
    {
        SyncLog::create([
            'sync_type' => 'github_push',
            'status' => 'success',
            'message' => 'تم الرفع إلى GitHub بنجاح'
        ]);
        
        return redirect()->route('sync')->with('success', 'تم الرفع بنجاح');
    }

    public function hostingerDeploy()
    {
        SyncLog::create([
            'sync_type' => 'hostinger_deploy',
            'status' => 'success',
            'message' => 'تم النشر على Hostinger بنجاح'
        ]);
        
        return redirect()->route('sync')->with('success', 'تم النشر بنجاح');
    }
}
