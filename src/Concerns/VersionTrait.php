<?php

namespace PcbFlow\CPQ\Concerns;

use PcbFlow\CPQ\Exceptions\RuntimeException;
use PcbFlow\CPQ\Models\Version;

trait VersionTrait
{
    /**
     * @param int $versionId
     * @return \PcbFlow\CPQ\Models\Version
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getVersionOrAbort($versionId)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        return $version;
    }

    /**
     * @param int $versionId
     * @return \PcbFlow\CPQ\Models\Version
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getEditableVersionOrAbort($versionId)
    {
        $version = $this->getVersionOrAbort($versionId);

        if (!$version->is_editable) {
            throw new RuntimeException('Version is not editable');
        }

        return $version;
    }

    /**
     * @param int $versionId
     * @return \PcbFlow\CPQ\Models\Version
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getDeletableVersionOrAbort($versionId)
    {
        $version = $this->getVersionOrAbort($versionId);

        if (!$version->is_deletable) {
            throw new RuntimeException('Version is not deletable');
        }

        return $version;
    }

    /**
     * @param int $versionId
     * @return \PcbFlow\CPQ\Models\Version
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getLockableVersionOrAbort($versionId)
    {
        $version = $this->getVersionOrAbort($versionId);

        if (!$version->is_lockable) {
            throw new RuntimeException('Version is not lockable');
        }

        return $version;
    }

    /**
     * @param int $versionId
     * @return \PcbFlow\CPQ\Models\Version
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getUnlockableVersionOrAbort($versionId)
    {
        $version = $this->getVersionOrAbort($versionId);

        if (!$version->is_unlockable) {
            throw new RuntimeException('Version is not unlockable');
        }

        return $version;
    }

    /**
     * @param int $versionId
     * @return \PcbFlow\CPQ\Models\Version
     * @throws \PcbFlow\CPQ\Exceptions\RuntimeException
     */
    public function getActivableVersionOrAbort($versionId)
    {
        $version = $this->getVersionOrAbort($versionId);

        if (!$version->is_activable) {
            throw new RuntimeException('Version is not activable');
        }

        return $version;
    }

    /**
     * @param \PcbFlow\CPQ\Models\Version $version
     * @return bool
     */
    public function pureDeleteVersion($version)
    {
        return $version->delete();
    }

    /**
     * @param \PcbFlow\CPQ\Models\Version $version
     * @return \PcbFlow\CPQ\Models\Version
     */
    public function pureReplicateVersion($version)
    {
        $newVersion = $version->replicate();

        $newVersion->save();

        return $newVersion;
    }
}
