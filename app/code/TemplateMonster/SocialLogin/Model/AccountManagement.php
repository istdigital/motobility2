<?php

namespace TemplateMonster\SocialLogin\Model;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Data\CustomerFactory as CustomerDataFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AccountManagement.
 */
class AccountManagement
{
    /**
     * @var OAuthTokenFactory
     */
    protected $tokenFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerDataFactory
     */
    protected $customerDataFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * AccountManagement constructor.
     *
     * @param CustomerDataFactory         $customerDataFactory
     * @param OAuthTokenFactory           $tokenFactory
     * @param CustomerFactory             $customerFactory
     * @param EventManagerInterface       $eventManager
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerDataFactory $customerDataFactory,
        OAuthTokenFactory $tokenFactory,
        CustomerFactory $customerFactory,
        EventManagerInterface $eventManager,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerDataFactory = $customerDataFactory;
        $this->tokenFactory = $tokenFactory;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * @param array $data
     *
     * @return CustomerInterface
     */
    public function authenticateByOAuth(array $data)
    {
        /** @var OAuthToken $token */
        $token = $this->tokenFactory->create();

        $token = $token->getByProvider($data['provider_code'], $data['provider_id']);
        try {
            $customer = $token->getId() ?
                $this->customerRepository->getById($token->getCustomerId()) :
                $this->customerRepository->get($data['email'])
            ;
            $isFirstLogin = false;
        } catch (NoSuchEntityException $e) {
            $customer = $this->customerDataFactory->create();
            foreach ($data as $key => $value) {
                $customer->setData($key, $value);
            }
            $customer = $this->customerRepository->save($customer);

            $isFirstLogin = true;
        }

        $token->addData([
            'provider_code' => $data['provider_code'],
            'provider_id' => $data['provider_id'],
            'customer_id' => $customer->getId(),
        ]);
        $token->save();

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            [
                'model' => $customerModel,
                'isUsingOAuth' => true,
                'isFirstLogin' => $isFirstLogin,
                'data' => $data,
            ]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }
}
