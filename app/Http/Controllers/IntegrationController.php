<?php
namespace App\Http\Controllers;
use App\Models\Integration;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function index()
    {
        $integrations = Integration::all();
        return view('integrations', compact('integrations'));
    }

    public function save(Request $request)
    {
        $serviceName = $request->input('service_name');
        $integration = Integration::where('service_name', $serviceName)->first();
        
        if ($integration) {
            $integration->is_enabled = $integration->is_enabled === 'true' ? 'false' : 'true';
            $integration->save();
        }
        
        return redirect()->route('integrations');
    }
}
