<?php
/**
 * @author        Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @copyright (c) 2019 by HubCulture Ltd.
 */

namespace Hub\UltraCore;

use Hub\UltraCore\Money\Currency;
use Hub\UltraCore\Money\CurrencyRate;

/**
 * This class is used when rebuilding the historical rates table from scratch.
 * The historical asset per each day should be set using the setter (setHistoricalAsset).
 *
 * @see     https://hubculture.com/admin/ultra/user-submissions/?s=&page=2&limit=10&o=DESC&of=is_approved&debug=0
 * @package Hub\UltraCore
 */
class HistoricalAssetAwareCurrencyRateProvider implements CurrencyRatesProviderInterface
{
    /**
     * @var UltraAssetsRepository
     */
    private $ultraAssetsRepository;

    /**
     * @var UltraAsset
     */
    private $historicalAsset;

    /**
     * HistoricalAssetAwareCurrencyRateProvider constructor.
     *
     * @param UltraAssetsRepository $ultraAssetsRepository
     * @param UltraAsset            $historicalAsset [optional] historical asset. You can use the setter to set it.
     */
    public function __construct(UltraAssetsRepository $ultraAssetsRepository, UltraAsset $historicalAsset = null)
    {
        $this->ultraAssetsRepository = $ultraAssetsRepository;
        $this->historicalAsset = $historicalAsset;
    }

    /**
     * @param UltraAsset $historicalAsset
     */
    public function setHistoricalAsset(UltraAsset $historicalAsset)
    {
        $this->historicalAsset = $historicalAsset;
    }

    /**
     * Use this to retrieve currency exchange rates for a given primary currency.
     * ex: if you want to find out how many US Dollars needed for 1 Ven, then you need to pass here 'VEN'
     *
     * @param Currency $primaryCurrency Primary currency.
     *
     * @return CurrencyRate[]
     */
    public function getByPrimaryCurrencySymbol(Currency $primaryCurrency)
    {
        return [
            new CurrencyRate(
                $this->historicalAsset->tickerSymbol(),
                $this->ultraAssetsRepository->getAssetAmountForOneVen($this->historicalAsset)->getAmount()
            ),
        ];
    }
}
