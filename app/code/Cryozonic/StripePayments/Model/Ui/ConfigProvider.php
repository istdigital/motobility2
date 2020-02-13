<?php

namespace Cryozonic\StripePayments\Model\Ui;

use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\ConfigProviderInterface;
use Cryozonic\StripePayments\Gateway\Http\Client\ClientMock;
use Magento\Framework\Locale\Bundle\DataBundle;
use Cryozonic\StripePayments\Helper\Logger;
use Cryozonic\StripePayments\Model\PaymentMethod;
use Cryozonic\StripePayments\Model\Config;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'cryozonic_stripe';
    const YEARS_RANGE = 15;

    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Cryozonic\StripePayments\Model\Config $config,
        \Magento\Customer\Model\Session $session,
        \Cryozonic\StripePayments\Helper\Generic $helper,
        \Cryozonic\StripePayments\Model\StripeCustomer $customer,
        \Cryozonic\StripePayments\Model\PaymentIntent $paymentIntent
    )
    {
        $this->localeResolver = $localeResolver;
        $this->_date = $date;
        $this->request = $request;
        $this->assetRepo = $assetRepo;
        $this->config = $config;
        $this->session = $session;
        $this->helper = $helper;
        $this->saveCards = $config->getSaveCards();
        $this->customer = $customer;
        $this->paymentIntent = $paymentIntent;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'enabled' => $this->config->isEnabled(),
                    'months' => $this->getMonths(),
                    'years' => $this->getYears(),
                    'cvvImageUrl' => $this->getCvvImageUrl(),
                    'securityMethod' => $this->config->getSecurityMethod(),
                    'useStoreCurrency' => $this->config->useStoreCurrency(),
                    'stripeJsKey' => $this->config->getPublishableKey(),
                    'showSaveCardOption' => $this->getShowSaveCardOption(),
                    'alwaysSaveCard' => $this->getAlwaysSaveCard(),
                    'savedCards' => $this->customer->getCustomerCards(),
                    'isApplePayEnabled' => (bool)$this->config->isApplePayEnabled(),
                    'applePayLocation' => $this->config->getApplePayLocation(),
                    'module' => Config::module()
                ]
            ]
        ];
    }

    public function getShowSaveCardOption()
    {
        return $this->config->getSaveCards() && $this->session->isLoggedIn();
    }

    public function getAlwaysSaveCard()
    {
        return $this->config->alwaysSaveCards();
    }

    /**
     * Retrieve list of months translation
     *
     * @return array
     * @api
     */
    public function getMonths()
    {
        $data = [];
        $months = (new DataBundle())->get(
            $this->localeResolver->getLocale()
        )['calendar']['gregorian']['monthNames']['format']['wide'];
        foreach ($months as $key => $value) {
            $monthNum = ++$key < 10 ? '0' . $key : $key;
            $data[$key] = $monthNum . ' - ' . $value;
        }
        return $data;
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     * @api
     */
    public function getYears()
    {
        $years = [];
        $first = (int)$this->_date->date('Y');
        for ($index = 0; $index <= self::YEARS_RANGE; $index++) {
            $year = $first + $index;
            $years[$year] = $year;
        }
        return $years;
    }

    /**
     * Retrieve CVV tooltip image url
     *
     * @return string
     */
    public function getCvvImageUrl()
    {
        return $this->getViewFileUrl('Magento_Checkout::cvv.png');
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }

    /**
     * Get icons for available payment methods
     *
     * @return array
     */
    // protected function getIcons()
    // {
    //     $icons = [];
    //     $types = $this->ccConfig->getCcAvailableTypes();
    //     foreach (array_keys($types) as $code) {
    //         if (!array_key_exists($code, $icons)) {
    //             $asset = $this->ccConfig->createAsset('Magento_Payment::images/cc/' . strtolower($code) . '.png');
    //             $placeholder = $this->assetSource->findRelativeSourceFilePath($asset);
    //             if ($placeholder) {
    //                 list($width, $height) = getimagesize($asset->getSourceFile());
    //                 $icons[$code] = [
    //                     'url' => $asset->getUrl(),
    //                     'width' => $width,
    //                     'height' => $height
    //                 ];
    //             }
    //         }
    //     }
    //     return $icons;
    // }
}
