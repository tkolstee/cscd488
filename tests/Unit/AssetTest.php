<?php

namespace Tests\Unit;

use App\Exceptions\AssetNotFoundException;
use Tests\TestCase;
use App\Models\Asset;
use App\Models\Assets\FirewallAsset;
use App\Models\Assets\SQLDatabaseAsset;
use App\Models\Assets\TestAttackAsset;
use App\Models\Assets\TestDefenseAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetTest extends TestCase {
    use RefreshDatabase;

    private function getAll(){
        $dir = opendir(dirname(__FILE__)."/../../app/Models/Assets");
        while(($asset = readdir($dir)) !== false){
            if($asset != "." && $asset != ".."){
                $length = strlen($asset);
                $asset = substr($asset, 0, $length - 4);
                $class = "\\App\\Models\\Assets\\" . $asset;
                $assets[] = new $class();
            }
        }
        return $assets;
    }

    private function getAllCount(){
        $assets = $this->getAll();
        return count($assets);
    }

    private function getBuyableBlueCount(){
        $allAssets = Asset::getAll();
        foreach($allAssets as $asset){
            if($asset->buyable == 1 && $asset->blue == 1){
                $assets[] = $asset;
            }
        }
        return count($assets);
    }

    private function getBuyableRedCount(){
        $allAssets = Asset::getAll();
        foreach($allAssets as $asset){
            if($asset->buyable == 1 && $asset->blue == 0){
                $assets[] = $asset;
            }
        }
        return count($assets);
    }

    public function testGetAllAssets() {
        $assets = Asset::getAll();
        $expectedCount = $this->getAllCount();
        $this->assertEquals($expectedCount, count($assets));
    }

    public function testGetAssetTags() {
        $assets = Asset::getAll();
        $tags = Asset::getTags($assets);
        foreach($assets as $asset){
            foreach($asset->tags as $tag){
                $this->assertContains($tag, $tags);
            }
        }
    }

    public function testAssetFilterByTag() { //Update this test when we have more tags on assets!
        $assets = collect(Asset::getAll());
        $tagFilter = "defensive tag 1"; //this requires testDefenseAsset and its tag
        $filtered = Asset::filterByTag($assets, $tagFilter);
        $this->assertEquals(1, count($filtered));
        $this->assertEquals(new TestDefenseAsset, $filtered->first());
    }

    public function testGetBuyableBlue() {
        $assets = Asset::getBuyableBlue();
        $expectedCount = $this->getBuyableBlueCount();
        $this->assertEquals($expectedCount, count($assets));
    }

    public function testGetBuyableRed() {
        $assets = Asset::getBuyableRed();
        $expectedCount = $this->getBuyableRedCount();
        $this->assertEquals($expectedCount, count($assets));
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
