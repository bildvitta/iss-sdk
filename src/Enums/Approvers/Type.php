<?php

namespace BildVitta\Hub\Enums\Approvers;

enum Type: string
{
    case DISCOUNT = 'discount';
    case CREDIT_POLICY = 'credit_policy';

    public function getLabel(): string
    {
        return match ($this) {
            self::DISCOUNT => __('Discount'),
            self::CREDIT_POLICY => __('Credit Policy'),
        };
    }

    public function isDiscount(): bool
    {
        return $this === self::DISCOUNT;
    }

    public function isCreditPolicy(): bool
    {
        return $this === self::CREDIT_POLICY;
    }

    public static function options(): array
    {
        $cases = [];
        foreach (self::cases() as $case) {
            $cases[] = [
                'label' => $case->getLabel(),
                'value' => $case->value,
            ];
        }

        return $cases;
    }
}