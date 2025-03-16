<?php
namespace MyApp\Skeletons;
abstract class TagSkeleton {
    protected ?int $tag_id;
    protected ?string $tag_name;
    protected ?int $tag_owner;

    abstract protected function validateTagData(string $tag_name, int $owner_id): void;
    abstract protected function validateOwnerId(int $owner_id): void;

    public function __construct(
        int $tag_id = null,
        string $tag_name = null,
        int $tag_owner = null
    ) {
        $this->tag_id = $tag_id;
        $this->tag_name = $tag_name;
        $this->tag_owner = $tag_owner;
    }

    // Getters and Setters
    public function getTagId(): ?int {
        return $this->tag_id;
    }
    public function setTagId(?int $tag_id): void {
        $this->tag_id = $tag_id;
    }

    public function getTagName(): ?string {
        return $this->tag_name;
    }
    public function setTagName(?string $tag_name): void {
        $this->tag_name = $tag_name;
    }

    public function getTagOwner(): ?int {
        return $this->tag_owner;
    }
    public function setTagOwner(?int $tag_owner): void {
        $this->tag_owner = $tag_owner;
    }
}
?>