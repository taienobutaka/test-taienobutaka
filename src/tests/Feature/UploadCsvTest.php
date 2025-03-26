<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Employee;
use PHPUnit\Framework\Attributes\Test;

class UploadCsvTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // テスト用のストレージを設定
        Storage::fake('local');
    }

    /** @test */
    public function it_can_upload_a_valid_csv_file()
    {
        // テスト用のCSVファイルを作成
        $csvContent = "name,birth_date,email,address\nJohn Doe,1990-01-01,john.doe@example.com,123 Main St\nJane Smith,1985-05-15,jane.smith@example.com,456 Elm St";
        $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

        // POSTリクエストを送信
        $response = $this->postJson(route('upload.csv'), [
            'csv_file' => $file,
        ]);

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertJson(['message' => 'CSVファイルがアップロードされました。']);

        // データベースの検証
        $this->assertDatabaseHas('employees', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        $this->assertDatabaseHas('employees', [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
        ]);
    }

    /** @test */
    public function it_fails_to_upload_an_invalid_csv_file()
    {
        // 無効なファイルを作成
        $file = UploadedFile::fake()->create('invalid.txt', 100);

        // POSTリクエストを送信
        $response = $this->postJson(route('upload.csv'), [
            'csv_file' => $file,
        ]);

        // レスポンスの検証
        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }
}
