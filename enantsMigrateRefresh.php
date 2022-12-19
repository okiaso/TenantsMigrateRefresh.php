<?php

namespace App\Console\Commands;

use Illuminate\Database\Console\Migrations\RefreshCommand;
use Stancl\Tenancy\Concerns\DealsWithMigrations;
use Stancl\Tenancy\Concerns\HasATenantsOption;

class TenantsMigrateRefresh extends RefreshCommand
{
    use HasATenantsOption, DealsWithMigrations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate-refresh {migrations?*}'; //  {--f|files=?}

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenants: Reset and re-run all migrations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (config('tenancy.migration_parameters') as $parameter => $value) {
            if (!$this->input->hasParameterOption($parameter)) {
                $this->input->setOption(ltrim($parameter, '-'), $value);
            }
        }

        if ($this->argument('migrations')) {
            $dir = config('tenancy.migration_parameters.--path')[0];

            $files = [];
            foreach ($this->argument('migrations') as $key => $file) {
                $files[$key] = $dir . '/' . $file;
            }

            $this->input->setOption('path', $files);
        }

        if (!$this->confirmToProceed()) {
            return;
        }

        tenancy()->runForMultiple($this->option('tenants'), function ($tenant) {
            $this->line("Tenant: {$tenant->getTenantKey()}");

            // Refresh
            parent::handle();
        });
    }
}
