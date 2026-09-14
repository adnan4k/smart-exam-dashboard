<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Videos become upload-only (external links can't be downloaded for offline
     * use) and move off the public disk so downloads can be gated by subscription.
     * Files keep their relative path — only the disk they live on changes.
     */
    public function up(): void
    {
        $this->moveFiles('public', 'local');

        Schema::table('videos', function (Blueprint $table) {
            $table->string('checksum', 32)->nullable()->after('file_size');
        });

        if (Schema::hasColumn('videos', 'source')) {
            Schema::table('videos', fn (Blueprint $t) => $t->dropColumn('source'));
        }
        if (Schema::hasColumn('videos', 'video_url')) {
            Schema::table('videos', fn (Blueprint $t) => $t->dropColumn('video_url'));
        }

        $this->backfillChecksums();
    }

    public function down(): void
    {
        $this->moveFiles('local', 'public');

        Schema::table('videos', function (Blueprint $table) {
            $table->enum('source', ['upload', 'url'])->default('url')->after('description');
            $table->string('video_url')->nullable()->after('file_path');
            $table->dropColumn('checksum');
        });
    }

    /**
     * Physically relocate stored video files between disks.
     */
    private function moveFiles(string $from, string $to): void
    {
        $fromDisk = Storage::disk($from);
        $toDisk   = Storage::disk($to);

        if (!$fromDisk->exists('videos')) {
            return;
        }

        foreach ($fromDisk->files('videos') as $path) {
            if (!$toDisk->exists($path)) {
                $toDisk->put($path, $fromDisk->get($path));
            }
            $fromDisk->delete($path);
        }
    }

    private function backfillChecksums(): void
    {
        $disk = Storage::disk('local');

        foreach (DB::table('videos')->whereNotNull('file_path')->get() as $row) {
            if (!$disk->exists($row->file_path)) {
                continue;
            }

            DB::table('videos')->where('id', $row->id)->update([
                'checksum'  => md5_file($disk->path($row->file_path)),
                'file_size' => $row->file_size ?: $disk->size($row->file_path),
            ]);
        }
    }
};
