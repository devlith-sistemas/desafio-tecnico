<?php

namespace Tests\Feature;

use App\Enums\StudentExportStatus;
use App\Models\StudentExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadStudentExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_owner_can_stream_a_completed_export(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        Storage::disk('local')->put('exports/students/test.xlsx', 'xlsx-content');

        $export = StudentExport::create([
            'requested_by_user_id' => $user->id,
            'status' => StudentExportStatus::Completed,
            'disk' => 'local',
            'path' => 'exports/students/test.xlsx',
            'file_name' => 'test.xlsx',
            'completed_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('student-exports.download', $export));

        $response->assertOk();
        $this->assertSame('xlsx-content', $response->streamedContent());
        $this->assertStringContainsString('test.xlsx', $response->headers->get('content-disposition'));
    }
}
