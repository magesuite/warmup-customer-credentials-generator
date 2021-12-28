<?php

namespace MageSuite\WarmupCustomerCredentialsGenerator\Service\Customer;

// phpcs:ignoreFile
class CustomerManager
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $groupManagement;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->customerResource = $customerResource;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->groupManagement = $groupManagement;
    }

    public function getCustomer(string $email): ?\Magento\Customer\Api\Data\CustomerInterface
    {
        try {
            return $this->customerRepository->get($email);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return null;
        }
    }

    public function createCustomer(int $storeId, int $groupId, string $username, string $password): \Magento\Customer\Model\Customer
    {
        if ($groupId === \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID) {
            throw new \LogicException('Cannot create customer account in not-logged-in group!');
        }

        $customer = $this->createCustomerObject(
            $storeId,
            $groupId,
            $username,
            $password
        );

        $this->customerResource->save($customer);

        return $customer;
    }

    protected function createCustomerObject(
        int $storeId,
        int $groupId,
        string $email,
        string $password
    ): \Magento\Customer\Model\Customer {
        return $this->customerFactory->create([
            'data' => $this->createCustomerData(
                $storeId,
                $groupId,
                $email,
                $password
            )
        ]);
    }

    protected function createCustomerData(
        int $storeId,
        int $groupId,
        string $email,
        string $password
    ): array {
        return [
            'prefix'        => $this->getCustomerRandomNamePrefix(),
            'firstname'     => 'WarmupCrawler',
            'lastname'      => sprintf('FakeCustomer-G%dS%d', $groupId, $storeId),
            'email'         => $email,
            'password'      => $password,
            'group_id'      => $groupId,
            'website_id'    => $this->getStore($storeId)->getWebsiteId(),
        ];
    }

    protected function getCustomerRandomNamePrefix(): string
    {
        static $prefixes = null;

        if (null === $prefixes) {
            if ($prefixOptions = trim($this->scopeConfig->getValue('customer/address/prefix_options'))) {
                $prefixes = explode(';', $prefixOptions);
            } else {
                $prefixes = ['Mr.', 'Ms.'];
            }
        }

        return $prefixes[array_rand($prefixes)];
    }

    protected function getStore(int $id): ?\Magento\Store\Api\Data\StoreInterface
    {
        return $this->storeManager->getStore($id);
    }
}
