<?php

namespace App\Controller\System;

use App\Controller\Core\Application;
use App\Entity\System\LockedResource;
use App\Entity\User;
use App\Services\Session\UserRolesSessionService;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LockedResourceController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @param Statement|null $stmt
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function isResourceLocked(string $record, string $type, string $target, Statement $stmt = null): bool
    {
        if( is_null($stmt) ){
            $stmt = $this->app->repositories->lockedResourceRepository->buildIsLockForRecordTypeAndTargetStatement();
        }

        switch($type){
            case LockedResource::TYPE_ENTITY:
                $isLockedResource = $this->app->repositories->lockedResourceRepository->executeIsLockForRecordTypeAndTargetStatement($stmt, $record, $type, $target);

                if( empty($isLockedResource) ){
                    return false;
                }
                return true;
            break;
            // in case of directory we need to check every parent directory for lock
            // if any parent is locked then we also lock given directory
            case LockedResource::TYPE_DIRECTORY:

                $pattern = "#(.*)[\/]{1}(.*)#";
                while( preg_match($pattern, $record, $matches) ){ # walk over the path and build parent path

                    $lockedResource = $this->app->repositories->lockedResourceRepository->executeIsLockForRecordTypeAndTargetStatement($stmt, $record, $type, $target);
                    if( !empty($lockedResource) ){
                        return true;
                    }

                    if( !array_key_exists(2, $matches) ){
                        return false;
                    }
                    $replace = DIRECTORY_SEPARATOR . preg_quote($matches[2]);
                    $record  = preg_replace("#{$replace}#", "", $record);

                }

                return false;

                break;
            default:
                throw new Exception("This locked resource type is not supported");
        }

    }

    /**
     * @return bool
     */
    public function isSystemLocked(): bool
    {
        return !UserRolesSessionService::hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @param bool $showFlashMessage
     * @param Statement|null $stmt
     * @return bool
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function isAllowedToSeeResource(string $record, string $type, string $target, bool $showFlashMessage = true, Statement $stmt = null): bool
    {
        $isResourceLocked = $this->isResourceLocked($record, $type, $target, $stmt);
        $isSystemLocked   = $this->isSystemLocked();

        if(
                ( $isResourceLocked && !$isSystemLocked )
            ||  ( !$isResourceLocked )
        ){
            return true;
        }

        if($showFlashMessage){
            $message = $this->app->translator->translate("responses.lockResource.youAreNotAllowedToSeeThisResource");
            $this->app->addDangerFlash($message);
        }
        return false;
    }

    /**
     * @param string $oldPath
     * @param string $newPath
     */
    public function updatePath(string $oldPath, string $newPath): void
    {
        $this->app->repositories->lockedResourceRepository->updatePath($oldPath, $newPath);
    }

    /**
     * Gets the LockedResource for entity name and record id
     * @param string $record
     * @param string $type
     * @param string $target
     * @return LockedResource|null
     */
    public function findOneEntity(string $record, string $type, string $target):? LockedResource
    {
        return $this->app->repositories->lockedResourceRepository->findOneEntity($record, $type, $target);
    }

    /**
     * @param LockedResource $lockedResource
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(LockedResource $lockedResource): void
    {
        $this->app->repositories->lockedResourceRepository->remove($lockedResource);
    }

    /**
     * @param LockedResource $lockedResource
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(LockedResource $lockedResource): void
    {
        $this->app->repositories->lockedResourceRepository->add($lockedResource);
    }

}
