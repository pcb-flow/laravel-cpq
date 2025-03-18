<?php

namespace PcbFlow\CPQ\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PcbFlow\CPQ\Concerns\CostTrait;
use PcbFlow\CPQ\Concerns\FactorTrait;
use PcbFlow\CPQ\Concerns\LeadtimeTrait;
use PcbFlow\CPQ\Concerns\ProductTrait;
use PcbFlow\CPQ\Concerns\VersionTrait;
use PcbFlow\CPQ\Models\Version;
use PcbFlow\CPQ\Validators\SaveVersionValidator;

class VersionService
{
    use VersionTrait, ProductTrait, FactorTrait, CostTrait, LeadtimeTrait;

    /**
     * @var int $page
     * @var int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateVersions($page, $perPage = 20)
    {
        return Version::orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param int $versionId
     * @param array $relations
     * @return \PcbFlow\CPQ\Models\Version
     */
    public function getVersion($versionId, $relations = [])
    {
        $version = $this->getVersionOrAbort($versionId);

        $version->load($relations);

        return $version;
    }

    /**
     * @param array $data
     * @return \PcbFlow\CPQ\Models\Version
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createVersion($data)
    {
        $validatedData = SaveVersionValidator::validate($data);

        return Version::create(array_merge($validatedData, [
            'uuid' => Str::uuid()->toString(),
            'is_locked' => false,
            'is_active' => false,
        ]));
    }

    /**
     * @param int $versionId
     * @param array $data
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateVersion($versionId, $data)
    {
        $version = $this->getEditableVersionOrAbort($versionId);

        $validatedData = SaveVersionValidator::validate($data);

        return $version->update($validatedData);
    }

    /**
     * @param int $versionId
     * @return bool
     */
    public function deleteVersion($versionId)
    {
        $version = $this->getDeletableVersionOrAbort($versionId);

        return DB::transaction(function () use ($version) {
            foreach ($version->products as $product) {
                $product->leadtimes->each(function ($leadtime) {
                    $this->pureDeleteLeadtime($leadtime);
                });

                $product->costs->each(function ($cost) {
                    $this->pureDeleteCost($cost);
                });

                $product->factors->each(function ($factor) {
                    $this->pureDeleteFactor($factor);
                });

                $this->pureDeleteProduct($product);
            }

            return $this->pureDeleteVersion($version);
        });
    }

    /**
     * @param int $versionId
     * @return bool
     */
    public function lockVersion($versionId)
    {
        $version = $this->getLockableVersionOrAbort($versionId);

        $version->is_locked = true;

        return $version->save();
    }

    /**
     * @param int $versionId
     * @return bool
     */
    public function unlockVersion($versionId)
    {
        $version = $this->getUnlockableVersionOrAbort($versionId);

        $version->is_locked = false;

        return $version->save();
    }

    /**
     * @param int $versionId
     * @return bool
     */
    public function activateVersion($versionId)
    {
        $version = $this->getActivableVersionOrAbort($versionId);

        $version->is_active = true;

        return $version->save();
    }

    /**
     * @param int $versionId
     * @return \PcbFlow\CPQ\Models\Version
     */
    public function replicateVersion($versionId)
    {
        $version = $this->getVersionOrAbort($versionId);

        return DB::transaction(function () use ($version) {
            $newVersion = $this->pureReplicateVersion($version);

            foreach ($version->products as $product) {
                $newProduct = $this->pureReplicateProduct($product, $newVersion->id);

                foreach ($product->factors as $factor) {
                    $this->pureReplicateFactor($factor, $newProduct->id);
                }

                foreach ($product->costs as $cost) {
                    $this->pureReplicateCost($cost, $newProduct->id);
                }

                foreach ($product->leadtimes as $leadtime) {
                    $this->pureReplicateLeadtime($leadtime, $newProduct->id);
                }
            }

            return $newVersion;
        });
    }
}
