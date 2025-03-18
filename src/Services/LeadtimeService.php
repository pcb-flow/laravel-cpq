<?php

namespace PcbFlow\CPQ\Services;

use Illuminate\Support\Facades\DB;
use PcbFlow\CPQ\Concerns\LeadtimeTrait;
use PcbFlow\CPQ\Concerns\ProductTrait;
use PcbFlow\CPQ\Models\Leadtime;
use PcbFlow\CPQ\Validators\SaveLeadtimeValidator;
use PcbFlow\CPQ\Validators\SortLeadtimeValidator;

class LeadtimeService
{
    use ProductTrait, LeadtimeTrait;

    /**
     * @param int $productId
     * @param array $data
     * @return \PcbFlow\CPQ\Models\Leadtime
     */
    public function createLeadtime($productId, $data)
    {
        $product = $this->getEditableProductOrAbort($productId);

        $validatedData = SaveLeadtimeValidator::validate($product->id, $data);

        return Leadtime::create(array_merge($validatedData, [
            'product_id' => $product->id,
        ]));
    }

    /**
     * @param int $leadtimeId
     * @param array $data
     * @return bool
     */
    public function updateLeadtime($leadtimeId, $data)
    {
        $leadtime = $this->getEditableLeadtimeOrAbort($leadtimeId);

        $validatedData = SaveLeadtimeValidator::validate($leadtime->product_id, $data, $leadtime->id);

        return $leadtime->update($validatedData);
    }

    /**
     * @param int $leadtimeId
     * @return bool
     */
    public function deleteLeadtime($leadtimeId)
    {
        $leadtime = $this->getDeletableLeadtimeOrAbort($leadtimeId);

        return $leadtime->delete();
    }

    /**
     * @param int $productId
     * @param array $data
     * @return bool
     */
    public function multisortLeadtimes($productId, $data)
    {
        $product = $this->getEditableProductOrAbort($productId);

        $validatedData = SortLeadtimeValidator::validate($data);

        $leadtimes = Leadtime::where('product_id', $product->id)->get();

        return DB::transaction(function () use ($leadtimes, $validatedData) {
            foreach ($validatedData as $leadtimeData) {
                $leadtime = $leadtimes->firstWhere('id', $leadtimeData['id']);

                if ($leadtime) {
                    $leadtime->sort_order = $leadtimeData['sort_order'];

                    $leadtime->save();
                }
            }

            return true;
        });
    }
}
