<?php

namespace App\Entity;

use App\Repository\EmailTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmailTemplateRepository::class)]
#[ORM\Table(name: 'email_templates')]
class EmailTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank]
    private ?string $subjectTemplate = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $htmlBodyTemplate = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $plainTextBodyTemplate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'emailTemplate', targetEntity: Notification::class)]
    private Collection $notifications;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSubjectTemplate(): ?string
    {
        return $this->subjectTemplate;
    }

    public function setSubjectTemplate(string $subjectTemplate): static
    {
        $this->subjectTemplate = $subjectTemplate;

        return $this;
    }

    public function getHtmlBodyTemplate(): ?string
    {
        return $this->htmlBodyTemplate;
    }

    public function setHtmlBodyTemplate(string $htmlBodyTemplate): static
    {
        $this->htmlBodyTemplate = $htmlBodyTemplate;

        return $this;
    }

    public function getPlainTextBodyTemplate(): ?string
    {
        return $this->plainTextBodyTemplate;
    }

    public function setPlainTextBodyTemplate(string $plainTextBodyTemplate): static
    {
        $this->plainTextBodyTemplate = $plainTextBodyTemplate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setEmailTemplate($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getEmailTemplate() === $this) {
                $notification->setEmailTemplate(null);
            }
        }

        return $this;
    }
}
