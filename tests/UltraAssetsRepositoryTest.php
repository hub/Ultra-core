<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @date   : 09-06-2018
 */

namespace Hub\UltraCore;

use Hub\UltraCore\Money\CurrencyRate;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class UltraAssetsRepositoryTest extends TestCase
{
    /** @var array */
    private $testCurrencies = [
        ['secondary_currency' => 'XAU', 'current_amount' => 0.0000762543],
        ['secondary_currency' => 'ETH', 'current_amount' => 0.0001658963],
        ['secondary_currency' => 'CAD', 'current_amount' => 0.1262628972],
    ];

    /** @var UltraAssetsRepository */
    private $sut;

    /** @var \mysqli|MockInterface */
    private $mysqliMock;

    public function setUp()
    {
        $testCurrencyRates = [];
        foreach ($this->testCurrencies as $testCurrency) {
            $testCurrencyRates[] = new CurrencyRate($testCurrency['secondary_currency'], $testCurrency['current_amount']);
        }
        $this->mysqliMock = Mockery::mock('\mysqli');
        $currencyRatesProviderMock = Mockery::mock('\Hub\UltraCore\CurrencyRatesProvider');
        $currencyRatesProviderMock
            ->shouldReceive('getByPrimaryCurrencySymbol')
            ->once()
            ->andReturn($testCurrencyRates);

        $this->sut = new UltraAssetsRepository($this->mysqliMock, $currencyRatesProviderMock);
    }

    /**
     * @test
     */
    public function shouldReturnTheCorrectAssetAmountUsingAllWeightingConfig()
    {
        $testWeightingsConfig = [
            new UltraAssetWeighting('CAD', 0, 37),
            new UltraAssetWeighting('ETH', 0, 13),
            new UltraAssetWeighting('XAU', 0, 50),
        ];

        $assetMock = Mockery::mock('\Hub\UltraCore\UltraAsset');
        $assetMock->shouldReceive('weightingType')->once();
        $assetMock->shouldReceive('weightings')->once()->andReturn($testWeightingsConfig);
        $expectedWeightings = [
            new UltraAssetWeighting($testWeightingsConfig[0]->currencyName(),
                $this->testCurrencies[2]['current_amount'], 37),
            new UltraAssetWeighting($testWeightingsConfig[1]->currencyName(),
                $this->testCurrencies[1]['current_amount'], 13),
            new UltraAssetWeighting($testWeightingsConfig[2]->currencyName(),
                $this->testCurrencies[0]['current_amount'], 50),
        ];
        $assetMock->shouldReceive('setWeightings')->once()->with($expectedWeightings);

        $this->sut->enrichAssetWeightingAmounts($assetMock);
    }

    /**
     * @test
     */
    public function shouldNotReturnWeightingWithAInvalidCurrencyWeightingConfig()
    {
        $testWeightingsConfig = [
            new UltraAssetWeighting('INVALID_CAD', 0, 37),
            new UltraAssetWeighting('INVALID_ETH', 0, 13),
            new UltraAssetWeighting('INVALID_XAU', 0, 50),
        ];

        $assetMock = Mockery::mock('\Hub\UltraCore\UltraAsset');
        $assetMock->shouldReceive('weightingType')->once();
        $assetMock->shouldReceive('weightings')->once()->andReturn($testWeightingsConfig);
        $expectedWeightings = []; // since we have requested weightings with wrong / non existing currency types, this is empty

        $assetMock->shouldReceive('setWeightings')->once()->with($expectedWeightings);
        $this->sut->enrichAssetWeightingAmounts($assetMock);
    }

    /**
     * @test
     */
    public function shouldReturnTheTotalValueOfTHeAssetUsingCurrencyWeightings()
    {
        $testWeightingsConfig = [
            new UltraAssetWeighting('CAD', $this->testCurrencies[2]['current_amount'], 37),
            new UltraAssetWeighting('ETH', $this->testCurrencies[1]['current_amount'], 13),
            new UltraAssetWeighting('XAU', $this->testCurrencies[0]['current_amount'], 50),
        ];

        $assetMock = Mockery::mock('\Hub\UltraCore\UltraAsset');
        $assetMock->shouldReceive('weightingType')->once()->andReturn(UltraAssetsRepository::TYPE_CURRENCY_COMBO);
        $assetMock->shouldReceive('tickerSymbol')->once();
        $assetMock->shouldReceive('weightings')->once()->andReturn($testWeightingsConfig);

        $expectedAssetValueInVen = // 0.046776965633000003
            (
                (($this->testCurrencies[2]['current_amount'] /* 0.1262628972 */ / 100) * 37)
                + (($this->testCurrencies[1]['current_amount'] /* 0.0001658963 */ / 100) * 13)
                + (($this->testCurrencies[0]['current_amount'] /* 0.0000762543 */ / 100) * 50)
            );

        // The test asset must be worth 0.0467 VEN
        $actualAssetValue = $this->sut->getAssetAmountForOneVen($assetMock);
        $this->assertSame($expectedAssetValueInVen, $actualAssetValue->getAmount());
        $this->assertTrue((0.046776965633 === $actualAssetValue->getAmount()));
    }

    /**
     * @test
     */
    public function shouldReturnTheTotalInVenForCustomVenAmounts()
    {
        // asset with custom ven amounts always contain one weighting
        $testWeightingsConfig = [new UltraAssetWeighting('Ven', 13, 100)];

        $assetMock = Mockery::mock('\Hub\UltraCore\UltraAsset');
        $assetMock->shouldReceive('weightingType')->once()->andReturn(UltraAssetsRepository::TYPE_EXTERNAL_ENTITY);
        $assetMock->shouldReceive('tickerSymbol')->once();
        $assetMock->shouldReceive('weightings')->once()->andReturn($testWeightingsConfig);

        $actualAssetValue = $this->sut->getAssetAmountForOneVen($assetMock);
        $this->assertSame(1 / 13.0 /* 0.07674597083653109 */, $actualAssetValue->getAmount());
    }
}
