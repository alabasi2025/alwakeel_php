<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\RoadmapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard (الصفحة الرئيسية)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Chat (الدردشة الذكية)
Route::get('/chat', [ChatController::class, 'index'])->name('chat');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

// Integrations (التكاملات)
Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations');
Route::post('/integrations/save', [IntegrationController::class, 'save'])->name('integrations.save');
Route::post('/integrations/openai/save', [IntegrationController::class, 'saveOpenAI'])->name('integrations.openai.save');
Route::post('/integrations/openai/test', [IntegrationController::class, 'testOpenAI'])->name('integrations.openai.test');
Route::post('/integrations/manus/save', [IntegrationController::class, 'saveManus'])->name('integrations.manus.save');
Route::post('/integrations/manus/test', [IntegrationController::class, 'testManus'])->name('integrations.manus.test');

// Sync Engine (محرك المزامنة)
Route::get('/sync', [SyncController::class, 'index'])->name('sync');
Route::post('/sync/github-pull', [SyncController::class, 'githubPull'])->name('sync.github-pull');
Route::post('/sync/github-push', [SyncController::class, 'githubPush'])->name('sync.github-push');
Route::post('/sync/hostinger-deploy', [SyncController::class, 'hostingerDeploy'])->name('sync.hostinger-deploy');

// Backup (النسخ الاحتياطي)
Route::get('/backup', [BackupController::class, 'index'])->name('backup');
Route::post('/backup/create', [BackupController::class, 'create'])->name('backup.create');
Route::get('/backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');

// Changelog (سجل التحديثات)
Route::get('/changelog', [ChangelogController::class, 'index'])->name('changelog');

// Roadmap (خارطة الطريق)
Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap');
Route::post('/roadmap/{item}/status', [RoadmapController::class, 'updateStatus'])->name('roadmap.update-status');
Route::post('/roadmap/{item}/progress', [RoadmapController::class, 'updateProgress'])->name('roadmap.update-progress');

// Conversation routes
Route::get('/conversations', [App\Http\Controllers\ConversationController::class, 'index']);
Route::post('/conversations', [App\Http\Controllers\ConversationController::class, 'store']);
Route::get('/conversations/{id}', [App\Http\Controllers\ConversationController::class, 'show']);
Route::delete('/conversations/{id}', [App\Http\Controllers\ConversationController::class, 'destroy']);
Route::delete('/conversations-all', [App\Http\Controllers\ConversationController::class, 'destroyAll']);

// Export routes (يجب أن تكون قبل {id} لتجنب التعارض)
Route::get('/export-all-conversations', [App\Http\Controllers\ExportController::class, 'exportAll'])->name('conversations.export-all');
Route::get('/conversations/{id}/export', [App\Http\Controllers\ExportController::class, 'exportConversation'])->name('conversations.export');

// Terminal Routes
Route::get('/terminal', [App\Http\Controllers\TerminalController::class, 'index'])->name('terminal');
Route::post('/terminal/execute', [App\Http\Controllers\TerminalController::class, 'execute']);
Route::get('/terminal/history', [App\Http\Controllers\TerminalController::class, 'history']);
Route::post('/terminal/clear', [App\Http\Controllers\TerminalController::class, 'clear']);
