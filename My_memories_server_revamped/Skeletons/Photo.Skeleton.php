<?php

class PhotoSkeleton {
    protected ?int $image_id;
    protected ?string $image_url;
    protected ?int $owner_id;
    protected ?string $title;
    protected ?string $date;
    protected ?string $description;
    protected ?int $tag_id;

    public function __construct(
        int $image_id = null,
        string $image_url = null,
        int $owner_id = null,
        string $title = null,
        string $date = null,
        string $description = null,
        int $tag_id = null
    ) {
        $this->image_id = $image_id;
        $this->image_url = $image_url;
        $this->owner_id = $owner_id;
        $this->title = $title;
        $this->date = $date;
        $this->description = $description;
        $this->tag_id = $tag_id;
    }

    // Getters and Setters
    public function getImageId(): ?int {
        return $this->image_id;
    }
    public function setImageId(?int $image_id): void {
        $this->image_id = $image_id;
    }

    public function getImageUrl(): ?string {
        return $this->image_url;
    }
    public function setImageUrl(?string $image_url): void {
        $this->image_url = $image_url;
    }

    public function getOwnerId(): ?int {
        return $this->owner_id;
    }
    public function setOwnerId(?int $owner_id): void {
        $this->owner_id = $owner_id;
    }

    public function getTitle(): ?string {
        return $this->title;
    }
    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function getDate(): ?string {
        return $this->date;
    }
    public function setDate(?string $date): void {
        $this->date = $date;
    }

    public function getDescription(): ?string {
        return $this->description;
    }
    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getTagId(): ?int {
        return $this->tag_id;
    }
    public function setTagId(?int $tag_id): void {
        $this->tag_id = $tag_id;
    }
}
?>