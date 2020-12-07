<?php

namespace Tests\Unit;

use App\Exceptions\AssetNotFoundException;
use Tests\TestCase;
use App\Models\Asset;
use App\Models\Assets\FirewallAsset;
use App\Models\Assets\SQLDatabaseAsset;
use App\Models\Assets\TestAttackAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetTest extends TestCase {
    use RefreshDatabase;

    public function ctestGetAllAssets() {
        $assets = Asset::getAll();
        $expectedAssets = [new FirewallAsset,
                        new SQLDatabaseAsset,
                        new TestAttackAsset,
                        new AdDeptAsset,
                        new SecurityMonitorAsset,
                        new PRDeptAsset,
                        new VPNAsset];
        $this->assertEquals($expectedAssets, $assets);
    }

    public function ctestGetBuyableBlue() {
        $assets = Asset::getBuyableBlue();
        $expectedAssets = [new FirewallAsset,
                        new SQLDatabaseAsset];
        $this->assertEquals($expectedAssets, $assets);
    }

    public function ctestGetBuyableRed() {
        $assets = Asset::getBuyableRed();
        $expectedAssets = [new TestAttackAsset];
        $this->assertEquals($expectedAssets, $assets);
    }

    public function testGetAsset() {
        $asset = Asset::get('Firewall');
        $expected = new FirewallAsset;
        $this->assertEquals($expected, $asset);
    }

    public function testGetInvalidAsset() {
        $this->expectException(AssetNotFoundException::class);
        Asset::get('NotARealAsset');
    }

    public function testGetAssetByName(){
        $asset = Asset::getByName('SQL Database');
        $expected = new SQLDatabaseAsset;
        $this->assertEquals($expected, $asset);
    }

    public function testGetAssetByNameInvalid(){
        $this->expectException(AssetNotFoundException::class);
        Asset::getByName('NotARealAsset');
    }
}
