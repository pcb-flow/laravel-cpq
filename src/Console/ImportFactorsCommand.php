<?php

namespace PcbFlow\CPQ\Console;

use Exception;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use PcbFlow\CPQ\Concerns\ProductTrait;
use PcbFlow\CPQ\Imports\FactorImport;

class ImportFactorsCommand extends Command
{
    use ProductTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpq:import-factors {product_id} {factor_file_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import CPQ factors from an excel file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $product = $this->getProduct();

            $filePath = $this->validateFactorFilePath();

            $this->importFactors($product, $filePath);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info('Import success');
    }

    /**
     * @return \PcbFlow\CPQ\Models\Product
     */
    protected function getProduct()
    {
        $product = $this->getEditableProductOrAbort($this->argument('product_id'));

        return $product;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function validateFactorFilePath()
    {
        $filePath = $this->argument('factor_file_path');

        if (!file_exists($filePath)) {
            throw new Exception('Factor file does not exist');
        }

        return $filePath;
    }

    /**
     * @param \PcbFlow\CPQ\Models\Product $product
     * @param string $filePath
     * @return void
     */
    protected function importFactors($product, $filePath)
    {
        Excel::import(new FactorImport($product), $filePath);
    }
}
