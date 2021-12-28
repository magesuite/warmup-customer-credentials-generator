<?php

namespace MageSuite\WarmupCustomerCredentialsGenerator\Service\Credentials;

interface CredentialsProviderInterface
{
    /**
     * Generates fake customer credentials for specified customer group and store view combo.
     *
     * Null return value indicates that no sign in is needed for given input values.
     *
     * ### Warning: The generated e-mail address must be guaranteed to not exist / be not routable!
     *
     * If the e-mail domain exists and is potentially controlled by some 3rd party a malicious actor
     * may intercept password reset e-mails (e.g. using catch-all) and break into one of the fake accounts.
     *
     * This is especially important if some of the customer groups used for warmup have special privileges
     * like discounted prices, free shipping, etc.
     *
     * Thus it's very important that the e-mail address domain is either:
     *  - Not routable at all.
     *  - Is controlled by a trusted entity (e.g. store operator)
     *
     * ### Note: Redundant customer accounts rationale
     *
     * Right now Magento allows for customer accounts may be global or limited to website scope.
     * Also, note that customer group may be limited to a set of specific stores (websites?).
     *
     * @see \Magento\Customer\Model\Config\Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE
     * @see https://docs.magento.com/user-guide/customers/account-scope.html
     *
     * In order to simplify the logic and avoid additional error-prone code the user always assigned
     * to a specific website. The website may have multiple stores, so we could use website instead of the store view,
     * however, the extra redudancy is cheap resource-wise and greatly improves code readability while allowing future
     * customizations like tailoring the account settings to the store-specific traits (market region, locale, ...).
     *
     * @param int $storeId
     * @param int|null $customerGroupId
     * @return Credentials
     */
    public function get(int $storeId, int $customerGroupId): ?Credentials;
}
