<?php
namespace App\Entity;

use App\Enum\ContentType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'content_items')]
#[ORM\UniqueConstraint(name: 'uniq_provider_external', columns: ['provider', 'external_id'])]
class ContentItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\Column(length:32)]
    private string $provider;

    #[ORM\Column(name:'external_id', length:64)]
    private string $externalId;

    #[ORM\Column(enumType: ContentType::class)]
    private ContentType $type;

    #[ORM\Column(length:255)]
    private string $title;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'contentItems')]
    #[ORM\JoinTable(name: 'content_item_tags')]
    private Collection $tags;

    #[ORM\Column(type:'json', nullable:true)]
    private ?array $raw = null;

    #[ORM\Column(nullable:true)] private ?int $views = null;
    #[ORM\Column(nullable:true)] private ?int $likes = null;
    #[ORM\Column(nullable:true)] private ?int $readingTime = null;
    #[ORM\Column(nullable:true)] private ?int $reactions = null;
    #[ORM\Column(length:16, nullable:true)] private ?string $duration = null;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column(type:'float')] private float $baseScore = 0.0;
    #[ORM\Column(type:'float')] private float $freshnessScore = 0.0;
    #[ORM\Column(type:'float')] private float $interactionScore = 0.0;
    #[ORM\Column(type:'float')] private float $finalScore = 0.0;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    // ----- getters / setters (minimumlar) -----
    public function getId(): ?int { return $this->id; }

    public function setProvider(string $p): self { $this->provider=$p; return $this; }
    public function getProvider(): string { return $this->provider; }

    public function setExternalId(string $e): self { $this->externalId=$e; return $this; }
    public function getExternalId(): string { return $this->externalId; }

    public function setType(ContentType $t): self { $this->type=$t; return $this; }
    public function getType(): ContentType { return $this->type; }

    public function setTitle(string $t): self { $this->title=$t; return $this; }
    public function getTitle(): string { return $this->title; }

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    public function clearTags(): self
    {
        $this->tags->clear();
        return $this;
    }

    public function setRaw(?array $r): self { $this->raw=$r; return $this; }
    public function getRaw(): ?array { return $this->raw; }

    public function setViews(?int $v): self { $this->views=$v; return $this; }
    public function getViews(): ?int { return $this->views; }

    public function setLikes(?int $v): self { $this->likes=$v; return $this; }
    public function getLikes(): ?int { return $this->likes; }

    public function setReadingTime(?int $v): self { $this->readingTime=$v; return $this; }
    public function getReadingTime(): ?int { return $this->readingTime; }

    public function setReactions(?int $v): self { $this->reactions=$v; return $this; }
    public function getReactions(): ?int { return $this->reactions; }

    public function setDuration(?string $d): self { $this->duration=$d; return $this; }
    public function getDuration(): ?string { return $this->duration; }

    public function setPublishedAt(\DateTimeImmutable $d): self { $this->publishedAt=$d; return $this; }
    public function getPublishedAt(): \DateTimeImmutable { return $this->publishedAt; }

    public function setBaseScore(float $v): self { $this->baseScore=$v; return $this; }
    public function getBaseScore(): float { return $this->baseScore; }

    public function setFreshnessScore(float $v): self { $this->freshnessScore=$v; return $this; }
    public function getFreshnessScore(): float { return $this->freshnessScore; }

    public function setInteractionScore(float $v): self { $this->interactionScore=$v; return $this; }
    public function getInteractionScore(): float { return $this->interactionScore; }

    public function setFinalScore(float $v): self { $this->finalScore=$v; return $this; }
    public function getFinalScore(): float { return $this->finalScore; }

    public function setCreatedAt(\DateTimeImmutable $d): self { $this->createdAt=$d; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function setUpdatedAt(\DateTimeImmutable $d): self { $this->updatedAt=$d; return $this; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
