<?php

namespace PcbFlow\CPQ\Services;

use Illuminate\Support\Facades\DB;
use PcbFlow\CPQ\Concerns\CostTrait;
use PcbFlow\CPQ\Concerns\ProductTrait;
use PcbFlow\CPQ\Models\Cost;
use PcbFlow\CPQ\Validators\SaveCostValidator;
use PcbFlow\CPQ\Validators\SortCostValidator;

class CostService
{
    use ProductTrait, CostTrait;

    /**
     * @param int $productId
     * @param array $data
     * @return \PcbFlow\CPQ\Models\Cost
     */
    public function createCost($productId, $data)
    {
        $product = $this->getEditableProductOrAbort($productId);

        $validatedData = SaveCostValidator::validate($product->id, $data);

        return DB::transaction(function () use ($product, $validatedData) {
            $cost = Cost::create(array_merge($validatedData, [
                'product_id' => $product->id
            ]));

            if (isset($validatedData['rules'])) {
                $cost->rules()->createMany($validatedData['rules']);
            }

            return $cost;
        });
    }

    /**
     * @param int $costId
     * @param array $data
     * @return bool
     */
    public function updateCost($costId, $data)
    {
        $cost = $this->getEditableCostOrAbort($costId);

        $validatedData = SaveCostValidator::validate($cost->product_id, $data, $cost->id);

        return DB::transaction(function () use ($cost, $validatedData) {
            $cost->update($validatedData);

            $originalRuleIds = $cost->rules()->pluck('id');

            if (isset($validatedData['rules'])) {
                foreach ($validatedData['rules'] as $ruleData) {
                    $cost->rules()->updateOrCreate(['id' => $ruleData['id'] ?? null], $ruleData);
                }

                $newRuleIds = collect($validatedData['rules'])->pluck('id');

                $diffRuleIds = $originalRuleIds->diff($newRuleIds);
            } else {
                $diffRuleIds = $originalRuleIds;
            }

            $cost->rules()->whereIn('id', $diffRuleIds)->delete();

            return true;
        });
    }

    /**
     * @param int $costId
     * @return bool
     */
    public function deleteCost($costId)
    {
        $cost = $this->getDeletableCostOrAbort($costId);

        return DB::transaction(function () use ($cost) {
            $cost->rules()->delete();

            return $cost->delete();
        });
    }

    /**
     * @param int $productId
     * @param array $data
     * @return bool
     */
    public function sortCosts($productId, $data)
    {
        $product = $this->getEditableProductOrAbort($productId);

        $validatedData = SortCostValidator::validate($data);

        $costs = Cost::where('product_id', $product->id)->get();

        return DB::transaction(function () use ($costs, $validatedData) {
            foreach ($validatedData as $costData) {
                $cost = $costs->firstWhere('id', $costData['id']);

                if ($cost) {
                    $cost->sort_order = $costData['sort_order'];

                    $cost->save();
                }
            }

            return true;
        });
    }
}
