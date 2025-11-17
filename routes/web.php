<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ChangelogController;
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
