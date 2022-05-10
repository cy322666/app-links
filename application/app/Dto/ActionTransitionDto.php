<?php

namespace App\Dto;

class ActionTransitionDto
{
    public float $cost;

    public ?string $os;
    public ?string $country;
    public ?string $campaignId;
    public ?string $clickId;
    public ?string $zoneId;
    public ?string $zoneType;

    public static function transform(array $params): ActionTransitionDto
    {
        $dto = new self();

        $dto->os   = self::validateOs($params['os'] ?? null);
        $dto->cost = self::validateCost($params['cost'] ?? null);
        $dto->country    = self::validateCountry($params['country'] ?? null);
        $dto->campaignId = self::validateCampaignId($params['campaignid'] ?? null);
        $dto->clickId    = self::validateClickId($params['clickid'] ?? null);
        $dto->zoneId     = self::validateZoneId($params['zoneid'] ?? null);
        $dto->zoneType   = self::validateZoneType($params['zone_type'] ?? null);

        return $dto;
    }

    private static function validateOs($os) : ?string
    {
        if ($os) {
            $os = str_replace(['$'], '', $os);

            if ($os == 'ios' || $os == 'android') {

                return $os;
            }
        }
        return null;
    }

    private static function validateCost(string|float|int|null $cost) : ?float
    {
        if ($cost && $cost !== '${cost}') {

            $cost = str_replace(['$'], '', $cost);

            return floatval($cost);
        }
    }

    private static function validateCountry(string|null $country) : ?string
    {
        if (is_string($country) && $country !== '${country}') {

            $country = str_replace(['$'], '', $country);

            return (string)$country;
        }
        return null;
    }

    private static function validateCampaignId(string|null $campaignId) : ?string
    {
        if ($campaignId && $campaignId !== '${campaignid}') {

            $campaignId = str_replace(['$'], '', $campaignId);

            return (string)$campaignId;
        }
        return null;
    }

    private static function validateClickId(string|null $clickId) : ?string
    {
        if ($clickId && $clickId !== '${SUBID}') {

            $clickId = str_replace(['$'], '', $clickId);

            return (string)$clickId;
        }
        return null;
    }

    private static function validateZoneId(string|null $zoneId) : ?string
    {
        if ($zoneId && $zoneId !== '${zoneid}') {

            $zoneId = str_replace(['$'], '', $zoneId);

            return (string)$zoneId;
        }
        return null;
    }

    private static function validateZoneType(string|null $zoneType) : ?string
    {
        if ($zoneType && $zoneType !== '${zone_type}') {

            $zoneType = str_replace(['$'], '', $zoneType);

            return (string)$zoneType;
        }
        return null;
    }
}
