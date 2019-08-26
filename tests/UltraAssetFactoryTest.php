<?php
/**
 * @author : Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date   : 01-07-2018
 */

namespace Hub\UltraCore;

use PHPUnit\Framework\TestCase;

class UltraAssetFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateUltraAssetObjectWithAllTheWeightings()
    {
        $testUltraAssetRawData = array(
            'id' => 1,
            'hash' => 'testHash',
            'title' => 'testTitle',
            'category' => 'testCategory',
            'ticker_symbol' => 'testTickerSymbol',
            'num_assets' => 11.0,
            'background_image' => 'testBackgroundImage',
            'icon_image' => 'testIconImage',
            'is_approved' => 1,
            'is_featured' => 0,
            'user_id' => 18495,
            'weighting_type' => 'weighting_type',
            'weightings' => '[{"type":"testBaseCurrencyTicker","amount":100}]',
            'created_at' => '2000-01-01 00:00:00',
        );

        $actualAssetObject = UltraAssetFactory::fromArray($testUltraAssetRawData);

        $this->assertSame(true, $actualAssetObject->isWithOneWeighting());
        $this->assertSame($testUltraAssetRawData['id'], $actualAssetObject->id());
        $this->assertSame($testUltraAssetRawData['hash'], $actualAssetObject->weightingHash());
        $this->assertSame($testUltraAssetRawData['title'], $actualAssetObject->title());
        $this->assertSame($testUltraAssetRawData['category'], $actualAssetObject->category());
        $this->assertSame($testUltraAssetRawData['ticker_symbol'], $actualAssetObject->tickerSymbol());
        $this->assertSame($testUltraAssetRawData['num_assets'], $actualAssetObject->numAssets());
        $this->assertSame($testUltraAssetRawData['background_image'], $actualAssetObject->backgroundImage());
        $this->assertSame($testUltraAssetRawData['icon_image'], $actualAssetObject->iconImage());
        $this->assertSame(boolval($testUltraAssetRawData['is_approved']), $actualAssetObject->isApproved());
        $this->assertSame(boolval($testUltraAssetRawData['is_featured']), $actualAssetObject->isFeatured());
        $this->assertSame($testUltraAssetRawData['user_id'], $actualAssetObject->authorityUserId());
        $this->assertSame($testUltraAssetRawData['weighting_type'], $actualAssetObject->weightingType());
        $this->assertSame('testBaseCurrencyTicker', $actualAssetObject->weightings()[0]->currencyName());
        $this->assertSame(0, $actualAssetObject->weightings()[0]->currencyAmount());
        $this->assertSame(100, $actualAssetObject->weightings()[0]->percentage());
        $this->assertSame($testUltraAssetRawData['created_at'], $actualAssetObject->submissionDate());
    }
}
