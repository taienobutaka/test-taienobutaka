<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

// ホームページを表示するルート
Route::get('/', function () {
    return view('welcome');
});

// CSVアップロードフォームを表示するルート
Route::get('/upload', [UploadController::class, 'showUploadForm'])->name('upload.form');

// CSVファイルをアップロードして処理するルート
Route::post('/upload', [UploadController::class, 'uploadCsv'])->name('upload.csv');
