<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Game;
use Tests\TestCase;
use App\Models\Assets\SQLDatabaseAsset;
use Auth;

class BlueStoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void{
        parent::setUp();
        if(Game::all()->isEmpty()){
            $game = new Game();
            $game->save();
        }
        $team = Team::factory()->create([
            'balance' => 1000,
        ]);
        $user = User::factory()->create([
            'blueteam' => $team->id,
            'leader' => 1,
        ]);
        $this->be($user);
    }

    //Store Tests

    public function testBlueTeamCanViewAssetsInStore()
    {
        $response = $this->actingAs(Auth::user())->get('/blueteam/store');
        $response->assertViewIs('blueteam.store');
        $response->assertSee("Access Control Audit");
    }

    public function testBlueFilterInStore(){
        $response = $this->post('/blueteam/filter');
        $response->assertViewIs('blueteam.store');
        $response->assertSee(["Access Control Audit", "Advertising Dept."]);
        $response = $this->post('/blueteam/filter',[
            'filter' => "Action"
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSee("Access Control Audit");
        $response->assertDontSee("Advertising Dept.");
        $response = $this->post('/blueteam/filter',[
            'filter' => "No Filter",
            'sort' => "purchase_cost"
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertDontSee("Access Control Audit");
    }

    //Buy Tests

    public function testBlueTeamCanAddToCart()
    {
        $results = [];
        $results += ["Firewall" => 1];
        $response = $this->post('/blueteam/buy', [
            'results' => $results,
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSeeInOrder(["Firewall", 1]);
    }

    public function testBlueTeamCanAddMultipleToCart()
    {
        $results = [];
        $results += ["Firewall" => 3];
        $response = $this->post('/blueteam/buy', [
            'results' => $results,
        ]);
        $response->assertViewIs('blueteam.store');
        $response->assertSeeInOrder(["Firewall", 3]);
    }

    public function testBlueTeamCanBuyAssets()
    {
        $team = Auth::user()->getBlueTeam();
        $results = [];
        $results += ["Firewall" => 1];
        $this->post('/blueteam/buy', [
            'results' => $results,
        ]);
        $expectedBalance = $team->balance - Asset::get("Firewall")->purchase_cost;
        $response = $this->get('/blueteam/endturn');
        $response->assertViewIs('blueteam.home');
        $response->assertSee('Revenue: ' . $expectedBalance);
    }

    public function testBlueCanCancelBuyCart(){
        $team = Auth::user()->getBlueTeam();
        $asset = new SQLDatabaseAsset;
        session(['buyCart' => [$asset->name]]);
        $response = $this->post('/blueteam/cancel',[
            'cart' => "buy",
            'cancel' => $asset->name
        ]);
        $response->assertViewIs('blueteam.store');
        $cart = session('buyCart');
        $this->assertEmpty($cart);
    }

    public function testBluePickTargetOnEndTurn(){
        $team = Auth::user()->getBlueTeam();
        $targetTeam = Team::factory()->red()->create();
        $results = [];
        $results += ["HeightenedAwareness" => 1];
        $this->post('/blueteam/buy', [
            'results' => $results,
        ]);
        $expectedBalance = $team->balance - Asset::get("HeightenedAwareness")->purchase_cost;
        $response = $this->get('/blueteam/endturn');
        $response->assertViewIs('blueteam.target');
        $response->assertSee($targetTeam->name);
    }

    public function testRemoveCartItem(){
        $team = Auth::user()->getBlueTeam();
        $response = $this->post('/blueteam/removecartitem');
        $response->assertViewIs('blueteam.removecart');
        $response->assertSee("You have enough money");
        $asset = new SQLDatabaseAsset;
        session(['buyCart' => [$asset->name]]);
        $response = $this->post('/blueteam/removecartitem');
        $response->assertViewIs('blueteam.removecart');
        $response->assertSee("You have enough money");
        $team->balance = 0;
        $team->update();
        $response = $this->post('/blueteam/removecartitem');
        $response->assertViewIs('blueteam.removecart');
        $response->assertSeeInOrder(["Total Cost: ". $asset->purchase_cost , $asset->name, $asset->purchase_cost]);
        $response = $this->post('/blueteam/removecartitem', [
            'results' => [$asset->name]
        ]);
        $this->assertEquals(null, session('buyCart'));
        $response->assertViewIs('blueteam.home');
    }

}