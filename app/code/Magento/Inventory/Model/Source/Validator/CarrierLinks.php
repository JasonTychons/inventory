<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Shipping\Model\Config;

/**
 * Check that carrier links is valid
 */
class CarrierLinks implements SourceValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * Shipping config
     *
     * @var Config
     */
    private $shippingConfig;

    /**
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory, Config $shippingConfig)
    {
        $this->validationResultFactory = $validationResultFactory;
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @inheritdoc
     */
    public function validate(SourceInterface $source): ValidationResult
    {
        $carrierLinks = $source->getCarrierLinks();

        $errors = [];
        if (null !== $carrierLinks) {
            if (!is_array($carrierLinks)) {
                $errors[] = __('"%field" must be list of SourceCarrierLinkInterface.', [
                    'field' => SourceInterface::CARRIER_LINKS
                ]);
            } elseif (count($carrierLinks) && $source->isUseDefaultCarrierConfig()) {
                $errors[] = __('You can\'t configure "%field" because you have chosen Global Shipping configuration.', [
                    'field' => SourceInterface::CARRIER_LINKS
                ]);
            }

            $availableCarriers = $this->shippingConfig->getAllCarriers();
            foreach ($carrierLinks as $carrierLink) {
                $carrierCode = $carrierLink->getCarrierCode();
                if (array_key_exists($carrierCode, $availableCarriers) === false) {
                    $errors[] = __('You can\'t configure  because carrier with code: "%carrier" don\'t exists.', [
                        'carrier' => $carrierCode
                    ]);
                }
            }
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
