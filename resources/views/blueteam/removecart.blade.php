<style>
.blueMiddleContainer{
    background: url('../images/h3Background.jpg'), repeat;
}
.blueMiddleContainer label{
    color:white;
}
h4{
    color:white;
}
</style>
<div class="blueMiddleContainer">
    <h4>Total Cost: {{$totalCost}}</h4>
    <h4>You have {{$blueteam->balance}}</h4>
    <h4>You have {{($totalCost - $blueteam->balance)}} excess in cart</h4>
    <form class="storeForm" method="POST" action="/blueteam/removecartitem">
    @csrf
        <?php $cart = session('buyCart'); ?>
        <table class="storeFormCancel table">
            <tbody>
                @foreach($cart as $item)
                    <tr>
                        <td>
                            <input type="checkbox" id="{{$item}}" name="results[]" value="{{ $item }}">
                            <label for="{{$item}}">{{$item}} {{App\Models\Asset::getByName($item)->purchase_cost}}</label>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">
            Remove Item
        </button>
    </form>
    <br>
</div>