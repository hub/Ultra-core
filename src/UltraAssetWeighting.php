<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @date   : 18-02-2018
 */

namespace Hub\UltraCore;

class UltraAssetWeighting
{
    private $currencyName;
    private $currencyAmount;
    private $percentage;

    /**
     * UltraAssetWeighting constructor.
     *
     * @param string $currencyName   The currency type. Ex: USD, GBP
     * @param string $currencyAmount The currency amount in VEN.
     * @param int    $percentage     Percentage of the current type.
     */
    public function __construct($currencyName, $currencyAmount, $percentage)
    {
        $this->currencyName = $currencyName;
        $this->currencyAmount = $currencyAmount;
        $this->percentage = $percentage;
    }

    public function currencyName()
    {
        return $this->currencyName;
    }

    public function currencyAmount($isRaw = true)
    {
        if ($isRaw) {
            return $this->currencyAmount;
        }

        return floatval($this->absoluteRoundUpToDecimalPlaces($this->currencyAmount));
    }

    public function percentage($isRaw = true)
    {
        if ($isRaw) {
            return $this->percentage;
        }

        return floatval($this->absoluteRoundUpToDecimalPlaces($this->percentage));
    }

    public function percentageAmount($isRaw = true)
    {
        $percentageAmount = ($this->currencyAmount($isRaw) / 100) * $this->percentage($isRaw);
        if ($isRaw) {
            return $percentageAmount;
        }

        return floatval($this->absoluteRoundUpToDecimalPlaces($percentageAmount));
    }

    public function toArray($isVerbose = false, $isRaw = true)
    {
        $percentageAmount = $this->percentageAmount($isRaw);
        $stats = [
            'currency_name' => $this->currencyName(),
            'currency_amount' => $this->currencyAmount($isRaw),
            'percentage' => $this->percentage(),
            'percentage_amount' => $percentageAmount,
        ];

        if ($isVerbose) {
            $stats['verbose_calc_percentage_amount'] = "{$this->currencyAmount()}({$this->currencyName()}) * 0.{$this->percentage()}";
            $stats['verbose_currency_amount'] = $this->currencyAmount();
            $stats['verbose_percentage_amount'] = number_format($percentageAmount, 10, '.', '');
        }

        return $stats;
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * returns an absolute precision value WITHOUT doing any round/ceil/floor
     *
     * @param string $amount    The amount in string notation
     * @param int    $precision the precision to maintain
     *
     * @return string
     */
    private function absoluteRoundUpToDecimalPlaces($amount, $precision = 4)
    {
        $amountParts = explode('.', $amount);
        if (count($amountParts) > 1) {
            $amount = $amountParts[0] . '.' . substr($amountParts[1], 0, $precision);
        }

        return $amount;
    }
}
