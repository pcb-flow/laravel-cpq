<?php

namespace PcbFlow\CPQ\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use PcbFlow\CPQ\Services\VersionService;

class CreateVersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpq:create-version {version_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CPQ version';

    /**
     * @var \PcbFlow\CPQ\Services\VersionService
     */
    protected $versionService;

    /**
     * @param \PcbFlow\CPQ\Services\VersionService $versionService
     */
    public function __construct(VersionService $versionService)
    {
        parent::__construct();

        $this->versionService = $versionService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $version = $this->versionService->createVersion([
                'name' => $this->argument('version_name'),
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();

            return $this->error(reset($errors)[0]);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info('Version Id: ' . $version->id);
        $this->info('Version Name: ' . $version->name);
        $this->info('Version UUID: ' . $version->uuid);
    }
}
