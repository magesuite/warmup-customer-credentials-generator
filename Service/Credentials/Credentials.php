<?php

namespace MageSuite\WarmupCustomerCredentialsGenerator\Service\Credentials;

class Credentials
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var int
     */
    protected $customerGroupId;

    public function __construct(
        string $username,
        string $password,
        int $storeId,
        int $customerGroupId
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->storeId = $storeId;
        $this->customerGroupId = $customerGroupId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function getCustomerGroupId(): int
    {
        return $this->customerGroupId;
    }

    public function setCustomerGroupId(int $customerGroupId): void
    {
        $this->customerGroupId = $customerGroupId;
    }
}
