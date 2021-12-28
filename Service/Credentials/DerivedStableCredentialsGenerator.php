<?php

namespace MageSuite\WarmupCustomerCredentialsGenerator\Service\Credentials;

class DerivedStableCredentialsGenerator implements CredentialsProviderInterface
{
    const CUSTOMER_EMAIL_COMMENT = 'warmup';
    const CUSTOMER_DOMAIN_PREFIX = 'cache-warmup';

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->storeManager = $storeManager;
    }

    protected function getInstallationSecretKey(): string
    {
        if (!$key = $this->deploymentConfig->get(\Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY)) {
            throw new \Magento\Framework\Exception\ConfigurationMismatchException(
                new \Magento\Framework\Phrase('Crypt key must be set to a non-empty unique value!')
            );
        }

        return $key;
    }

    protected function getInstallationHostname(): string
    {
        return parse_url( // phpcs:ignore
            $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
            PHP_URL_HOST
        );
    }

    protected function getEmail(int $storeId, int $customerGroupId): string
    {
        return sprintf(
            's%d-cg%d+%s@%s.%s',
            $storeId,
            $customerGroupId,
            self::CUSTOMER_EMAIL_COMMENT,
            self::CUSTOMER_DOMAIN_PREFIX,
            $this->getInstallationHostname()
        );
    }

    protected function getPassword(int $storeId, int $customerGroupId): string
    {
        return sha1(implode('$$', [
            $storeId,
            $customerGroupId,
            $this->getInstallationHostname(),
            $this->getInstallationSecretKey(),
        ]));
    }

    /**
     * {@inheritDoc}
     */
    public function get(int $storeId, int $customerGroupId): ?Credentials
    {
        return new Credentials(
            $this->getEmail($storeId, $customerGroupId),
            $this->getPassword($storeId, $customerGroupId),
            $storeId,
            $customerGroupId
        );
    }
}
