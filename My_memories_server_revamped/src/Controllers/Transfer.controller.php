<?php

class TransferController {
    private $photoSkeleton;
    private $tagSkeleton;
    private $userSkeleton;

    public function __construct(
        PhotoSkeleton $photoSkeleton,
        TagSkeleton $tagSkeleton,
        UserSkeleton $userSkeleton
    ) {
        $this->photoSkeleton = $photoSkeleton;
        $this->tagSkeleton = $tagSkeleton;
        $this->userSkeleton = $userSkeleton;
    }
}
?>