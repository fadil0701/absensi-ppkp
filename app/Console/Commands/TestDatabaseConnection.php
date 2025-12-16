<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\Satpelkes;

class TestDatabaseConnection extends Command
{
    protected $signature = 'test:db';
    protected $description = 'Test database connection and basic queries';

    public function handle()
    {
        $this->info('Testing database connection...');
        
        try {
            // Test connection
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
            
            // Test tables (MySQL syntax)
            if (DB::getDriverName() === 'mysql') {
                $tables = DB::select('SHOW TABLES');
                $tableCount = count($tables);
            } else {
                $tableCount = DB::select("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table'")[0]->count ?? 0;
            }
            $this->info('✅ Tables found: ' . $tableCount);
            
            // Test models
            $pegawaiCount = Pegawai::count();
            $satpelkesCount = Satpelkes::count();
            
            $this->info("✅ Pegawai count: {$pegawaiCount}");
            $this->info("✅ Satpelkes count: {$satpelkesCount}");
            
            // Test stored procedure (MySQL only)
            if (DB::getDriverName() === 'mysql') {
                $result = DB::select('SHOW PROCEDURE STATUS WHERE Db = DATABASE()');
                $this->info('✅ Stored procedures: ' . count($result));
                
                // Test function
                $functions = DB::select('SHOW FUNCTION STATUS WHERE Db = DATABASE()');
                $this->info('✅ Functions: ' . count($functions));
            } else {
                $this->info('⚠️  Stored procedures/functions only available on MySQL');
            }
            
            $this->info("\n🎉 All tests passed!");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}

