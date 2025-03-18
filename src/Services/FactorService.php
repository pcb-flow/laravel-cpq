<?php

namespace PcbFlow\CPQ\Services;

use Illuminate\Support\Facades\DB;
use PcbFlow\CPQ\Concerns\FactorTrait;
use PcbFlow\CPQ\Concerns\ProductTrait;
use PcbFlow\CPQ\Models\Factor;
use PcbFlow\CPQ\Validators\SaveFactorValidator;
use PcbFlow\CPQ\Validators\SortFactorValidator;

class FactorService
{
    use ProductTrait, FactorTrait;

    /**
     * @param int $productId
     * @param array $data
     * @return \PcbFlow\CPQ\Models\Factor
     */
    public function createFactor($productId, $data)
    {
        $product = $this->getEditableProductOrAbort($productId);

        $validatedData = SaveFactorValidator::validate($product->id, $data);

        return DB::transaction(function () use ($product, $validatedData) {
            $factor = Factor::create(array_merge($validatedData, [
                'product_id' => $product->id
            ]));

            if ($validatedData['is_optional'] && isset($validatedData['options'])) {
                $factor->options()->createMany($validatedData['options']);
            }

            return $factor;
        });
    }

    /**
     * @param int $factorId
     * @param array $data
     * @return bool
     */
    public function updateFactor($factorId, $data)
    {
        $factor = $this->getEditableFactorOrAbort($factorId);

        $validatedData = SaveFactorValidator::validate($factor->product_id, $data, $factor->id);

        return DB::transaction(function () use ($factor, $validatedData) {
            $factor->update($validatedData);

            $originalOptionIds = $factor->options()->pluck('id');

            if ($factor->is_optional && isset($validatedData['options'])) {
                foreach ($validatedData['options'] as $optionData) {
                    $factor->options()->updateOrCreate(['id' => $optionData['id'] ?? null], $optionData);
                }

                $newOptionIds = collect($validatedData['options'])->pluck('id');

                $diffOptionIds = $originalOptionIds->diff($newOptionIds);
            } else {
                $diffOptionIds = $originalOptionIds;
            }

            $factor->options()->whereIn('id', $diffOptionIds)->delete();

            return true;
        });
    }

    /**
     * @param int $factorId
     * @return bool
     */
    public function deleteFactor($factorId)
    {
        $factor = $this->getDeletableFactorOrAbort($factorId);

        return DB::transaction(function () use ($factor) {
            $factor->options()->delete();

            return $factor->delete();
        });
    }

    /**
     * @param int $productId
     * @param array $data
     * @return bool
     */
    public function sortFactors($productId, $data)
    {
        $product = $this->getEditableProductOrAbort($productId);

        $validatedData = SortFactorValidator::validate($data);

        $factors = Factor::with('options')->where('product_id', $product->id)->get();

        return DB::transaction(function () use ($factors, $validatedData) {
            foreach ($validatedData as $factorData) {
                $factor = $factors->firstWhere('id', $factorData['id']);

                if ($factor) {
                    $factor->sort_order = $factorData['sort_order'];

                    $factor->save();

                    if ($factor->is_optional && isset($factorData['options'])) {
                        foreach ($factorData['options'] as $optionData) {
                            $option = $factor->options->firstWhere('id', $optionData['id']);

                            if ($option) {
                                $option->sort_order = $optionData['sort_order'];
                                $option->save();
                            }
                        }
                    }
                }
            }

            return true;
        });
    }
}
