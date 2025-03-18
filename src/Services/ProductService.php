<?php

namespace PcbFlow\CPQ\Services;

use Illuminate\Support\Facades\DB;
use PcbFlow\CPQ\Concerns\CostTrait;
use PcbFlow\CPQ\Concerns\FactorTrait;
use PcbFlow\CPQ\Concerns\LeadtimeTrait;
use PcbFlow\CPQ\Concerns\ProductTrait;
use PcbFlow\CPQ\Concerns\VersionTrait;
use PcbFlow\CPQ\Models\Product;
use PcbFlow\CPQ\Validators\SaveProductValidator;
use PcbFlow\CPQ\Validators\SortProductValidator;

class ProductService
{
    use VersionTrait, ProductTrait, FactorTrait, CostTrait, LeadtimeTrait;

    /**
     * @param int $versionId
     * @param array $data
     * @return \PcbFlow\CPQ\Models\Product
     */
    public function createProduct($versionId, $data)
    {
        $version = $this->getEditableVersionOrAbort($versionId);

        $validatedData = SaveProductValidator::validate($version->id, $data);

        return Product::create(array_merge($validatedData, [
            'version_id' => $version->id,
        ]));
    }

    /**
     * @param int $productId
     * @param array $data
     * @return bool
     */
    public function updateProduct($productId, $data)
    {
        $product = $this->getEditableProductOrAbort($productId);

        $validatedData = SaveProductValidator::validate($product->version_id, $data, $product->id);

        return $product->update($validatedData);
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function deleteProduct($productId)
    {
        $product = $this->getDeletableProductOrAbort($productId);

        return DB::transaction(function () use ($product) {
            $product->leadtimes->each(function ($leadtime) {
                $this->pureDeleteLeadtime($leadtime);
            });

            $product->costs->each(function ($cost) {
                $this->pureDeleteCost($cost);
            });

            $product->factors->each(function ($factor) {
                $this->pureDeleteFactor($factor);
            });

            return $this->pureDeleteProduct($product);
        });
    }

    /**
     * @param int $versionId
     * @param array $data
     * @return bool
     */
    public function sortProducts($versionId, $data)
    {
        $version = $this->getEditableVersionOrAbort($versionId);

        $validatedData = SortProductValidator::validate($data);

        $products = Product::where('version_id', $version->id)->get();

        return DB::transaction(function () use ($products, $validatedData) {
            foreach ($validatedData as $productData) {
                $product = $products->firstWhere('id', $productData['id']);

                if ($product) {
                    $product->sort_order = $productData['sort_order'];

                    $product->save();
                }
            }

            return true;
        });
    }
}
