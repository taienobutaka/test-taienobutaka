<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function showUploadForm()
    {
        return view('upload');
    }

    public function uploadCsv(Request $request)
    {
        Log::info('uploadCsvメソッドが呼び出されました。');
        Log::info('リクエスト内容:', $request->all());

        $validator = Validator::make($request->all(), [
            'csv_files.*' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            Log::error('バリデーションエラー:', $validator->errors()->all());
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        foreach ($request->file('csv_files') as $file) {
            $data = array_map('str_getcsv', file($file->getRealPath()));

            foreach ($data as $index => $row) {
                if ($index === 0) {
                    // Skip header row
                    continue;
                }

                Employee::create([
                    'name' => $row[0],
                    'birth_date' => $row[1],
                    'email' => $row[2],
                    'address' => $row[3],
                ]);
            }
        }

        return response()->json([
            'message' => 'すべてのCSVファイルがアップロードされました。',
        ], 200);
    }
}
