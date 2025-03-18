<?php

namespace PcbFlow\CPQ\Concerns;

use PcbFlow\CPQ\Exceptions\RuntimeException;
use PcbFlow\CPQ\Models\Factor;
use PcbFlow\CPQ\Models\Product;
use PcbFlow\CPQ\Models\Version;

trait FactorTrait
{
    /**
     * @param int $factorId
     * @return \PcbFlow\CPQ\Models\Factor
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getFactorOrAbort($factorId)
    {
        $factor = Factor::find($factorId);

        if (!$factor) {
            throw new RuntimeException('Factor not found');
        }

        return $factor;
    }

    /**
     * @param int $factorId
     * @return \PcbFlow\CPQ\Models\Factor
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getEditableFactorOrAbort($factorId)
    {
        $factor = $this->getFactorOrAbort($factorId);

        $product = Product::find($factor->product_id);

        if (!$product) {
            throw new RuntimeException("Factor's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Factor's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Factor is not editable');
        }

        return $factor;
    }

    /**
     * @param int $factorId
     * @return \PcbFlow\CPQ\Models\Factor
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getDeletableFactorOrAbort($factorId)
    {
        $factor = $this->getFactorOrAbort($factorId);

        $product = Product::find($factor->product_id);

        if (!$product) {
            throw new RuntimeException("Factor's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Factor's version not found");
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Factor is not deletable');
        }

        return $factor;
    }

    /**
     * @param \PcbFlow\CPQ\Models\Factor $factor
     * @return bool
     */
    public function pureDeleteFactor($factor)
    {
        $factor->options()->delete();

        return $factor->delete();
    }

    /**
     * @param \PcbFlow\CPQ\Models\Factor $factor
     * @param int $productId
     * @return \PcbFlow\CPQ\Models\Factor
     */
    public function pureReplicateFactor($factor, $productId)
    {
        $newFactor = $factor->replicate();

        $newFactor->product_id = $productId;

        $newFactor->save();

        foreach ($factor->options as $option) {
            $newOption = $option->replicate();

            $newOption->factor_id = $newFactor->id;

            $newOption->save();
        }

        return $newFactor;
    }
}
