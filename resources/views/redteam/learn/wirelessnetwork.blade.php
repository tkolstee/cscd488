@extends('redteam.base')

@section('title', 'Wireless Network Attack Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/wirelessnetwork">
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">
        @if ($step != 1)
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary" name="stepChange" value="-1">
                        Previous
                    </button>
                </div>
            </div>
        @endif

        @if ($step == 1)
            <h4>What is a wireless network attack?</h4>
            <p>
                A wireless network attack is when an organizations Wi-Fi is exploited <br>
                in some way. This attack does not involve any hardware or physical <br>
                tampering. Poor Wi-Fi security can compromise private data the organization <br>
                holds. It can also be a danger to customers who may use the wireless network.
            </p>

        @elseif ($step == 2)
            <h4>What vulnerabilities are exploited?</h4>
            <p>
                Wi-Fi routers using default passwords can be exploited by attackers. Traffic <br>
                on a wireless network may be encrypted, but some protocols, such as WEP <br>
                may be vulnerable. Attackers may also do packet sniffing on wireless networks <br>
                to observe the traffic. 
            </p>

        @elseif ($step == 3)
            <h4>Possible results of a wireless network attack</h4>
            <p>
                A successful wireless network attack can give an attacker a foothold in an <br>
                organization and access to information they're not supposed to have access to. <br>
                It can also lead to other attacks, such as a man-in-the-middle attack. In the <br>
                real world, some attackers set up fake Wi-Fi access points and wait for users <br>
                to connect. There are too many ways an attack can be performed on a wireless <br>
                network for us to go over here. 
            </p>

        @endif
        <!-- Reference:   -->
        @if ($step != 3)
            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary" name="stepChange" value="1">
                        Next
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>
@endsection