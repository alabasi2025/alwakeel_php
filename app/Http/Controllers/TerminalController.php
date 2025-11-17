<?php

namespace App\Http\Controllers;

use App\Models\TerminalCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class TerminalController extends Controller
{
    public function index()
    {
        $recentCommands = TerminalCommand::orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        return view('terminal', compact('recentCommands'));
    }

    public function execute(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
            'shell_type' => 'required|in:powershell,cmd,bash',
            'working_directory' => 'nullable|string'
        ]);

        $command = $request->input('command');
        $shellType = $request->input('shell_type');
        $workingDir = $request->input('working_directory', getcwd());

        // حفظ الأمر في قاعدة البيانات
        $terminalCommand = TerminalCommand::create([
            'shell_type' => $shellType,
            'command' => $command,
            'working_directory' => $workingDir,
            'status' => 'pending'
        ]);

        try {
            $startTime = microtime(true);

            // تنفيذ الأمر حسب نوع Shell
            $result = $this->executeCommand($command, $shellType, $workingDir);

            $executionTime = microtime(true) - $startTime;

            // تحديث السجل
            $terminalCommand->update([
                'output' => $result['output'],
                'exit_code' => $result['exit_code'],
                'execution_time' => $executionTime,
                'status' => $result['exit_code'] === 0 ? 'success' : 'error'
            ]);

            return response()->json([
                'success' => true,
                'output' => $result['output'],
                'exit_code' => $result['exit_code'],
                'execution_time' => round($executionTime, 3),
                'command_id' => $terminalCommand->id
            ]);

        } catch (\Exception $e) {
            $terminalCommand->update([
                'output' => $e->getMessage(),
                'status' => 'error',
                'exit_code' => 1
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function executeCommand($command, $shellType, $workingDir)
    {
        // تحديد Shell المناسب
        $shell = match($shellType) {
            'powershell' => PHP_OS_FAMILY === 'Windows' ? 'powershell.exe' : 'pwsh',
            'cmd' => 'cmd.exe',
            'bash' => 'bash',
            default => 'powershell.exe'
        };

        // تنفيذ الأمر
        if ($shellType === 'powershell') {
            $fullCommand = "$shell -NoProfile -Command \"cd '$workingDir'; $command\"";
        } elseif ($shellType === 'cmd') {
            $fullCommand = "$shell /c \"cd /d $workingDir && $command\"";
        } else {
            $fullCommand = "$shell -c \"cd '$workingDir' && $command\"";
        }

        // تنفيذ الأمر والحصول على النتيجة
        $output = [];
        $exitCode = 0;
        
        exec($fullCommand . ' 2>&1', $output, $exitCode);
        
        return [
            'output' => implode("\n", $output),
            'exit_code' => $exitCode
        ];
    }

    public function history()
    {
        $commands = TerminalCommand::orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($commands);
    }

    public function clear()
    {
        TerminalCommand::truncate();
        
        return response()->json([
            'success' => true,
            'message' => 'تم مسح سجل الأوامر'
        ]);
    }
}
