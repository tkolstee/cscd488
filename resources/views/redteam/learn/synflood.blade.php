@extends('redteam.base')

@section('title', 'Syn Flood Learning Page')

@section('pagecontent')
    <form method="POST" action="/learn/synflood">
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
        <h2>What is a SYN flood attack?</h2>
        <p>A SYN flood is a Denial-of-Service attack, also called a DDos attack, <br>
        that attempts to exhaust all of the servers resources in order to prevent <br>
        new legitimate connections.</p>
        <h2>TCP Protocol</h2>
        <p>Every connection using the TCP protocol uses a three-way handshake in order to <br>
        initiate communication.</p>
        <ol type="1">
        <li>The client sends a SYN packet to the server to request a new connection</li>
        <li>The server acknowledges this request by sending a SYN-ACK package back to the client</li>
        <li>Once the client has received the SYN-ACK package it finally responds with an ACK package</li></ol>
    @elseif ($step == 2)
        <h2>How does a SYN flood attack work?</h2>
        <p>A SYN flood attack works by exploiting the process of TCP Protocol's <br>
        three-way handshake. This denial-of-service attack exploits the fact that <br>
        the server sends a SYN-ACK package and then waits for an ACK package.</p>
        <p>To exploit this, the attacker sends a massive number of SYN packets <br>
        to the server at a very fast rate. This will cause the server to <br>
        use all the available connections and send back a massive number of SYN-ACK <br>
        packets. The connections are then unavailable until the attacker <br>
        replies with the final ACK packets or the server wait times out.</p>
    @elseif ($step == 3)
        <h2>Ways to improve a SYN flood attack</h2>
        <p>A big problem with the process of a SYN flood attack is <br>
        that, after the attacker floods the server with SYN packets, <br>
        the server then floods the attacker with SYN-ACK packets.</p>
        <p>A common way attackers fix this is by sending the SYN packets <br>
        with a fake IP address. This way the SYN-ACK packets are not <br>
        sent back to the correct computer preventing the attacker from being flooded.</p>
        <p>Other ways to prevent the attacker from being flooded includes:</p>
        <ul><li>Using a botnet to send the SYN packets</li>
        <li>Using a firewall to prevent outgoing packets other than SYN packets</li>
        <li>Using a firewall to prevent incoming SYN-ACK packets</li></ul>
    @elseif ($step == 4)
        <h2>Ways to prevent SYN flood attacks</h2>
        <p>Increasing the backlog queue is a way to help against SYN flood attacks. <br>
        The backlog queue is how many half-open requests a server can have and extending <br>
        this amount means that the server can handle more SYN packets. Increasing the <br>
        backlog queue, however, requires additional memory resources and if the server <br>
        does not have enough than the performance of the system will be decreased.</p>
        <p>Recycling the oldest half-open connection is another way of preventing <br>
        SYN flood attacks. When the backlog queue is filled, the oldest connection will <br>
        be terminated and the new incoming SYN packet will be put in the queue. <br>
        In order for this to work the new legitimate connection must be established <br>
        faster than the attacker's SYN packets fill the backlog queue. </p>
        <p>SYN cookies can be used as a way to prevent SYN flood attacks as well. <br>
        The server responds, like normal, with a SYN-ACK packet, but the SYN packet <br>
        in the backlog queue will be dropped. This makes room for more connections <br>
        in the backlog queue so it will never be filled. When the final ACK packet <br>
        is received, the SYN backlog queue entry will be reconstructed losing only <br>
        little information about the TCP connection.</p>
    @endif
    @if ($step != 4)
        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary" name="stepChange" value="1">
                    Next
                </button>
            </div>
        </div>
    @endif
    </form>
@endsection
