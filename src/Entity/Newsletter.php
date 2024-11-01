<?php

namespace WaHelp\Newsletter\Entity;

use WaHelp\Newsletter\Enum\NewsletterStatusEnum;

class Newsletter
{
    protected ?int $id = null;

    public function __construct(
        protected string $name,
        protected string $text,
        protected ?NewsletterStatusEnum $statusEnum = null
    ) {
    }

    public function getStatusEnum(): ?NewsletterStatusEnum
    {
        return $this->statusEnum;
    }

    public function setStatusEnum(?NewsletterStatusEnum $statusEnum): static
    {
        $this->statusEnum = $statusEnum;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }
}