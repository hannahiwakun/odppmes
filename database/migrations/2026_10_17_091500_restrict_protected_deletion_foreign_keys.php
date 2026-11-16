<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace ON DELETE CASCADE with ON DELETE RESTRICT for records that must
 * not disappear when a parent row is removed.
 *
 * Left unchanged:
 * - users.mda_id ON DELETE SET NULL
 * - audit_logs.user_id ON DELETE SET NULL
 * - projects.development_partner_id ON DELETE SET NULL
 * - project_mda.project_id ON DELETE CASCADE
 *
 * project_mda.project_id stays CASCADE because those rows belong to the
 * project. The application already allows a project delete when no
 * monitoring records, documents, or photos exist, and it does not detach
 * participating ministries first. Restricting that key would turn an
 * allowed project delete into a database error.
 *
 * This file is prepared for review. Do not run it against MySQL until
 * odppmes_db has a confirmed backup.
 *
 * After it is applied, confirm the result with the read-only queries in
 * database/scripts/verify_deletion_foreign_keys.sql.
 */
return new class extends Migration
{
    /**
     * Child column => referenced table.
     *
     * @var array<string, array{table: string, column: string, references: string}>
     */
    private array $protectedKeys = [
        ['table' => 'projects', 'column' => 'mda_id', 'references' => 'mdas'],
        ['table' => 'project_mda', 'column' => 'mda_id', 'references' => 'mdas'],
        ['table' => 'monitorings', 'column' => 'project_id', 'references' => 'projects'],
        ['table' => 'monitorings', 'column' => 'user_id', 'references' => 'users'],
        ['table' => 'project_documents', 'column' => 'project_id', 'references' => 'projects'],
        ['table' => 'project_documents', 'column' => 'uploaded_by', 'references' => 'users'],
        ['table' => 'project_photos', 'column' => 'project_id', 'references' => 'projects'],
        ['table' => 'project_photos', 'column' => 'uploaded_by', 'references' => 'users'],
    ];

    public function up(): void
    {
        foreach ($this->protectedKeys as $key) {
            $this->replaceDeleteRule($key['table'], $key['column'], $key['references'], 'RESTRICT');
        }
    }

    public function down(): void
    {
        foreach ($this->protectedKeys as $key) {
            $this->replaceDeleteRule($key['table'], $key['column'], $key['references'], 'CASCADE');
        }
    }

    private function replaceDeleteRule(string $table, string $column, string $referencedTable, string $onDelete): void
    {
        $constraint = "{$table}_{$column}_foreign";

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // MySQL 8.4 rejects DROP and ADD of the same constraint name in
            // one statement (error 1826). The add is separate, and a failed
            // add restores the rule that was just dropped.
            $restore = $onDelete === 'RESTRICT' ? 'CASCADE' : 'RESTRICT';

            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                $table,
                $constraint
            ));

            try {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE %s ON UPDATE NO ACTION',
                    $table,
                    $constraint,
                    $column,
                    $referencedTable,
                    $onDelete
                ));
            } catch (Throwable $exception) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE %s ON UPDATE NO ACTION',
                    $table,
                    $constraint,
                    $column,
                    $referencedTable,
                    $restore
                ));

                throw $exception;
            }

            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $referencedTable, $onDelete) {
            $blueprint->dropForeign([$column]);
            $blueprint->foreign($column)
                ->references('id')
                ->on($referencedTable)
                ->onDelete(strtolower($onDelete))
                ->onUpdate('no action');
        });
    }
};
