<?php

namespace PcbFlow\CPQ\Concerns;

use PcbFlow\CPQ\Exceptions\RuntimeException;
use PcbFlow\CPQ\Models\Leadtime;
use PcbFlow\CPQ\Models\Product;
use PcbFlow\CPQ\Models\Version;

trait LeadtimeTrait
{
    /**
     * @param int $leadtimeId
     * @return \PcbFlow\CPQ\Models\Leadtime
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getLeadtimeOrAbort($leadtimeId)
    {
        $leadtime = Leadtime::find($leadtimeId);

        if (!$leadtime) {
            throw new RuntimeException('Leadtime not found');
        }

        return $leadtime;
    }

    /**
     * @param int $leadtimeId
     * @return \PcbFlow\CPQ\Models\Leadtime
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getEditableLeadtimeOrAbort($leadtimeId)
    {
        $leadtime = $this->getLeadtimeOrAbort($leadtimeId);

        $product = Product::find($leadtime->product_id);

        if (!$product) {
            throw new RuntimeException("Leadtime's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Leadtime's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Leadtime is not editable');
        }

        return $leadtime;
    }

    /**
     * @param int $leadtimeId
     * @return \PcbFlow\CPQ\Models\Leadtime
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getDeletableLeadtimeOrAbort($leadtimeId)
    {
        $leadtime = $this->getLeadtimeOrAbort($leadtimeId);

        $product = Product::find($leadtime->product_id);

        if (!$product) {
            throw new RuntimeException("Leadtime's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Leadtime's version not found");
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Leadtime is not deletable');
        }

        return $leadtime;
    }

    /**
     * @param \PcbFlow\CPQ\Models\Leadtime $leadtime
     * @return bool
     */
    public function pureDeleteLeadtime($leadtime)
    {
        return $leadtime->delete();
    }

    /**
     * @param \PcbFlow\CPQ\Models\Leadtime $leadtime
     * @param int $productId
     * @return \PcbFlow\CPQ\Models\Leadtime
     */
    public function pureReplicateLeadtime($leadtime, $productId)
    {
        $newLeadtime = $leadtime->replicate();

        $newLeadtime->product_id = $productId;

        $newLeadtime->save();

        return $newLeadtime;
    }
}
