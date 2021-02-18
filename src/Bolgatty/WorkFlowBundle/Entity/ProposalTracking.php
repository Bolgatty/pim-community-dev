<?php
namespace Bolgatty\WorkFlowBundle\Entity;

class ProposalTracking
{
    const TYPE_PRODUCT = 'product';
    const TYPE_PRODUCT_MODEL = 'product_model';

    const STATUS_APPROVED = 'approved';
    const STATUS_REFUSED = 'refused';

    /** @var string */
    private $entityType;

    /** @var int */
    private $entityId;

    /** @var \DateTime */
    private $eventDate;

    /** @var array */
    private $payload;

    public function __construct(string $entityType, int $entityId, \DateTime $eventDate, array $payload)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->eventDate = $eventDate;
        $this->payload = $payload;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getEventDate(): \DateTime
    {
        return $this->eventDate;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
