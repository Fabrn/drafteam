<?php

namespace App\Entity;

use App\Bridge\Discord\Entity\DiscordProfile;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    public Uuid $identifier;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $username {
        get => $this->username ?? $this->discordProfile->globalUsername ?? $this->discordProfile->username;
    }

    #[ORM\Column(type: 'date_point')]
    public DatePoint $createdAt;

    #[ORM\Column(type: 'date_point', nullable: true)]
    public ?DatePoint $lastlyLoggedInAt = null;

    #[ORM\Column(type: 'date_point', nullable: true)]
    public ?DatePoint $deletedAt = null;

    #[ORM\Embedded]
    public DiscordProfile $discordProfile;

    /**
     * @var Collection<int, Draft>
     */
    #[ORM\OrderBy(['createdAt' => Order::Descending->value])]
    #[ORM\OneToMany(targetEntity: Draft::class, mappedBy: 'createdBy')]
    public Collection $createdDrafts;

    public function __construct()
    {
        $this->identifier = Uuid::v7();
        $this->createdAt = new DatePoint();
        $this->discordProfile = new DiscordProfile();
        $this->createdDrafts = new ArrayCollection();
    }

    public function getRoles(): array
    {
        return [
            Role::User->value,
        ];
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier->toString();
    }
}
