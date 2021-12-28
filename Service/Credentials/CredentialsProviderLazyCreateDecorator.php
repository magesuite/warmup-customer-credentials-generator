<?php

namespace MageSuite\WarmupCustomerCredentialsGenerator\Service\Credentials;

class CredentialsProviderLazyCreateDecorator implements CredentialsProviderInterface
{
    /**
     * @var CredentialsProviderInterface
     */
    protected $upstreamProvider;

    /**
     * @var \MageSuite\WarmupCustomerCredentialsGenerator\Service\Customer\CustomerManager
     */
    protected $accountCreator;

    public function __construct(
        CredentialsProviderInterface $upstreamProvider,
        \MageSuite\WarmupCustomerCredentialsGenerator\Service\Customer\CustomerManager $accountCreator
    ) {
        $this->upstreamProvider = $upstreamProvider;
        $this->accountCreator = $accountCreator;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get(int $storeId, int $customerGroupId = null): ?Credentials
    {
        if (null === $customerGroupId) {
            return null;
        }

        $credentials = $this->upstreamProvider->get($storeId, $customerGroupId);
        $customer = $this->accountCreator->getCustomer($credentials->getUsername());

        if (!$customer) {
            $customer = $this->accountCreator->createCustomer(
                $storeId,
                $customerGroupId,
                $credentials->getUsername(),
                $credentials->getPassword()
            );
        }

        if ($customer->getEmail() !== $credentials->getUsername()) {
            throw new \LogicException('Created customer account e-mail does not match the credentials username!');
        }

        return $credentials;
    }
}
