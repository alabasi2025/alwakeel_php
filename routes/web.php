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
Route::get('/roadmap/alabasi', [RoadmapController::class, 'alabasi'])->name('roadmap.alabasi');
Route::get('/roadmap/wakeel', [RoadmapController::class, 'wakeel'])->name('roadmap.wakeel');
Route::get('/roadmap/integration', [RoadmapController::class, 'integration'])->name('roadmap.integration');
Route::get('/roadmap/{item}', [RoadmapController::class, 'show'])->name('roadmap.show');
Route::post('/roadmap/{item}/status', [RoadmapController::class, 'updateStatus'])->name('roadmap.update-status');
Route::post('/roadmap/{item}/progress', [RoadmapController::class, 'updateProgress'])->name('roadmap.update-progress');

// Feature Details (تفاصيل الميزات)
Route::get('/features/{item}/edit', [App\Http\Controllers\FeatureDetailController::class, 'edit'])->name('features.edit');
Route::post('/features/{item}/update', [App\Http\Controllers\FeatureDetailController::class, 'update'])->name('features.update');
Route::post('/features/{item}/upload-before', [App\Http\Controllers\FeatureDetailController::class, 'uploadBeforeImage'])->name('features.upload-before');
Route::post('/features/{item}/upload-after', [App\Http\Controllers\FeatureDetailController::class, 'uploadAfterImage'])->name('features.upload-after');
Route::post('/features/{item}/upload-demo', [App\Http\Controllers\FeatureDetailController::class, 'uploadDemoGif'])->name('features.upload-demo');
Route::post('/features/{item}/upload-screenshot', [App\Http\Controllers\FeatureDetailController::class, 'uploadScreenshot'])->name('features.upload-screenshot');
Route::delete('/media/{media}', [App\Http\Controllers\FeatureDetailController::class, 'deleteMedia'])->name('media.delete');

// Manual Generator (توليد الأدلة)
Route::get('/manuals', [App\Http\Controllers\ManualGeneratorController::class, 'index'])->name('manuals.index');
Route::post('/manuals/generate', [App\Http\Controllers\ManualGeneratorController::class, 'generate'])->name('manuals.generate');
Route::get('/manuals/{manual}', [App\Http\Controllers\ManualGeneratorController::class, 'show'])->name('manuals.show');
Route::post('/manuals/{manual}/publish', [App\Http\Controllers\ManualGeneratorController::class, 'publish'])->name('manuals.publish');
Route::post('/manuals/{manual}/unpublish', [App\Http\Controllers\ManualGeneratorController::class, 'unpublish'])->name('manuals.unpublish');
Route::get('/manuals/{manual}/export-pdf', [App\Http\Controllers\ManualGeneratorController::class, 'exportPdf'])->name('manuals.export-pdf');
Route::get('/manuals/{manual}/export-word', [App\Http\Controllers\ManualGeneratorController::class, 'exportWord'])->name('manuals.export-word');
Route::delete('/manuals/{manual}', [App\Http\Controllers\ManualGeneratorController::class, 'destroy'])->name('manuals.destroy');
Route::post('/manuals/{manual}/send-to-alabasi', [App\Http\Controllers\ManualGeneratorController::class, 'sendToAlabasi'])->name('manuals.send-to-alabasi');
Route::post('/manuals/preview', [App\Http\Controllers\ManualGeneratorController::class, 'preview'])->name('manuals.preview');

// Update Logs (سجل التحديثات)
Route::get('/updates', [App\Http\Controllers\UpdateLogController::class, 'index'])->name('updates.index');
Route::get('/updates/{update}', [App\Http\Controllers\UpdateLogController::class, 'show'])->name('updates.show');
Route::post('/updates', [App\Http\Controllers\UpdateLogController::class, 'store'])->name('updates.store');
Route::put('/updates/{update}', [App\Http\Controllers\UpdateLogController::class, 'update'])->name('updates.update');
Route::delete('/updates/{update}', [App\Http\Controllers\UpdateLogController::class, 'destroy'])->name('updates.destroy');
Route::post('/updates/sync-git', [App\Http\Controllers\UpdateLogController::class, 'syncWithGit'])->name('updates.sync-git');
Route::get('/updates/export', [App\Http\Controllers\UpdateLogController::class, 'export'])->name('updates.export');

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
