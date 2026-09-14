<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings databases that already ran the original create migration in line with
 * the fixed one.
 *
 * contest_attempts was created with TIMESTAMP columns. That worked on servers
 * running explicit_defaults_for_timestamp = 1 (MySQL 8's default) and failed on
 * the rest, because MySQL hands every TIMESTAMP NOT NULL column after the first
 * an implicit '0000-00-00 00:00:00' default that NO_ZERO_DATE rejects. The
 * create migration now uses DATETIME, so this only has work to do where the old
 * version already ran - everywhere else it is a no-op.
 */
return new class extends Migration
{
    /** Column => nullability, in the order the table declares them. */
    private array $columns = [
        'started_at'   => 'NOT NULL',
        'expires_at'   => 'NOT NULL',
        'submitted_at' => 'NULL',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('contest_attempts') || ! $this->isMysql()) {
            return;
        }

        foreach ($this->columns as $column => $nullability) {
            if ($this->currentType($column) === 'timestamp') {
                DB::statement("ALTER TABLE `contest_attempts` MODIFY `{$column}` DATETIME {$nullability}");
            }
        }
    }

    /**
     * Deliberately empty. Putting the TIMESTAMP columns back would fail with the
     * very error this migration exists to fix on any server running with
     * explicit_defaults_for_timestamp = 0, and there is nothing to restore -
     * dropping the table is the create migration's job.
     */
    public function down(): void
    {
        //
    }

    private function isMysql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function currentType(string $column): ?string
    {
        $row = DB::selectOne(
            'SELECT DATA_TYPE AS data_type
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME = ?',
            ['contest_attempts', $column]
        );

        return $row ? strtolower($row->data_type) : null;
    }
};
