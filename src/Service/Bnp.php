<?php

namespace Gentor\BnpPF\Service;


/**
 * Class Bnp
 *
 * @package Gentor\BnpPF\Service
 */
class Bnp
{
    /**
     * @var int
     */
    protected $merchant;

    /**
     * @var Client
     */
    protected $client;

    /**
     * BnpService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->merchant = $config['merchant_id'];
        $this->client = new Client(
            $config['certificate'],
            $config['private_key'],
            $config['password'],
            $config['test_mode']
        );
    }

    /**
     * @param float     $price
     * @param array     $goods
     * @param float|int $down_payment
     *
     * @return mixed
     * @throws \Gentor\BnpPF\Service\Error
     */
    public function getPricingSchemes($price, array $goods, $down_payment = 0)
    {
        $urlParams = implode('/', [
            'GetAvailablePricingSchemes',
            $this->merchant,
            implode(',', $goods),
            $price,
            $down_payment,
        ]);

        return $this->getResultData($this->client->getResult($urlParams), 'PricingScheme');
    }

    /**
     * @param float     $price
     * @param array     $goods
     * @param int       $scheme_id
     * @param float|int $down_payment
     * @param float|int $installment
     *
     * @return mixed
     * @throws \Gentor\BnpPF\Service\Error
     */
    public function getPricingVariants($price, array $goods, $scheme_id, $down_payment = 0, $installment = 0)
    {
        $urlParams = implode('/', [
            'GetAvailablePricingVariants',
            $this->merchant,
            implode(',', $goods),
            $price,
            $down_payment,
            $installment,
            $scheme_id,
        ]);

        return $this->getResultData($this->client->getResult($urlParams), 'PricingVariant');
    }

    /**
     * @param float     $price
     * @param array     $goods
     * @param int       $variant_id
     * @param float|int $down_payment
     *
     * @return mixed
     * @throws \Gentor\BnpPF\Service\Error
     */
    public function calculateLoan($price, array $goods, $variant_id, $down_payment = 0)
    {
        $urlParams = implode('/', [
            'CalculateLoan',
            $this->merchant,
            implode(',', $goods),
            $price,
            $down_payment,
            $variant_id,
        ]);

        return $this->getResultData($this->client->getResult($urlParams), 'CreditProposition');
    }

    /**
     * @return mixed
     * @throws \Gentor\BnpPF\Service\Error
     */
    public function getGoodCategories()
    {
        $urlParams = implode('/', [
            'GetGoodCategories',
            $this->merchant,
        ]);

        return $this->client->getResult($urlParams);
    }

    /**
     * @param int $category_id
     *
     * @return mixed
     * @throws \Gentor\BnpPF\Service\Error
     */
    public function getGoodTypes($category_id)
    {
        $urlParams = implode('/', [
            'GetGoodTypes',
            $category_id,
        ]);

        return $this->client->getResult($urlParams);
    }

    /**
     * @param $object
     * @param $attribute
     *
     * @return array
     * @throws \Gentor\BnpPF\Service\Error
     */
    protected function getResultData($object, $attribute)
    {
        if (is_null($object->Data)) {
            if (!empty($object->ErrorCode)) {
                throw new Error($object->ErrorMessage, $object->ErrorCode, $object->ErrorDetails);
            }
            return [];
        }

        return $object->Data->{$attribute};
    }

}