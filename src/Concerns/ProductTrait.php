<?php

namespace PcbFlow\CPQ\Concerns;

use PcbFlow\CPQ\Exceptions\RuntimeException;
use PcbFlow\CPQ\Models\Product;
use PcbFlow\CPQ\Models\Version;

trait ProductTrait
{
    /**
     * @param int $productId
     * @return \PcbFlow\CPQ\Models\Product
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getProductOrAbort($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new RuntimeException('Product not found');
        }

        return $product;
    }

    /**
     * @param int $productId
     * @return \PcbFlow\CPQ\Models\Product
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getEditableProductOrAbort($productId)
    {
        $product = $this->getProductOrAbort($productId);

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Product's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Product is not editable');
        }

        return $product;
    }

    /**
     * @param int $productId
     * @return \PcbFlow\CPQ\Models\Product
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getDeletableProductOrAbort($productId)
    {
        $product = $this->getProductOrAbort($productId);

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Product's version not found");
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Product is not deletable');
        }

        return $product;
    }

    /**
     * @param \PcbFlow\CPQ\Models\Product $product
     * @return bool
     */
    public function pureDeleteProduct($product)
    {
        return $product->delete();
    }

    /**
     * @param \PcbFlow\CPQ\Models\Product $product
     * @param int $versionId
     * @return \PcbFlow\CPQ\Models\Product
     */
    public function pureReplicateProduct($product, $versionId)
    {
        $newProduct = $product->replicate();

        $newProduct->version_id = $versionId;

        $newProduct->save();

        return $newProduct;
    }
}
