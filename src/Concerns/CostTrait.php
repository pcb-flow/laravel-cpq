<?php

namespace PcbFlow\CPQ\Concerns;

use PcbFlow\CPQ\Exceptions\RuntimeException;
use PcbFlow\CPQ\Models\Cost;
use PcbFlow\CPQ\Models\Product;
use PcbFlow\CPQ\Models\Version;

trait CostTrait
{
    /**
     * @param int $costId
     * @return \PcbFlow\CPQ\Models\Cost
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getCostOrAbort($costId)
    {
        $cost = Cost::find($costId);

        if (!$cost) {
            throw new RuntimeException('Cost not found');
        }

        return $cost;
    }

    /**
     * @param int $costId
     * @return \PcbFlow\CPQ\Models\Cost
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getEditableCostOrAbort($costId)
    {
        $cost = $this->getCostOrAbort($costId);

        $product = Product::find($cost->product_id);

        if (!$product) {
            throw new RuntimeException("Cost's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Cost's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Cost is not editable');
        }

        return $cost;
    }

    /**
     * @param int $costId
     * @return \PcbFlow\CPQ\Models\Cost
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getDeletableCostOrAbort($costId)
    {
        $cost = $this->getCostOrAbort($costId);

        $product = Product::find($cost->product_id);

        if (!$product) {
            throw new RuntimeException("Cost's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Cost's version not found");
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Cost is not deletable');
        }

        return $cost;
    }

    /**
     * @param \PcbFlow\CPQ\Models\Cost $cost
     * @return bool
     */
    public function pureDeleteCost($cost)
    {
        $cost->rules()->delete();

        return $cost->delete();
    }

    /**
     * @param \PcbFlow\CPQ\Models\Cost $cost
     * @param int $productId
     * @return \PcbFlow\CPQ\Models\Cost
     */
    public function pureReplicateCost($cost, $productId)
    {
        $newCost = $cost->replicate();

        $newCost->product_id = $productId;

        $newCost->save();

        foreach ($cost->rules as $rule) {
            $newRule = $rule->replicate();

            $newRule->cost_id = $newCost->id;

            $newRule->save();
        }

        return $newCost;
    }
}
