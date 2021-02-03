@extends('redteam.base')

@section('title', 'Denial of Service Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/dos">
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
            <h4>What is a denial-of-service (DoS) attack?</h4>
            <p>
                An attack is considered a denial-of-service attack when regular users <br>
                are unable to use network resources they would normally have access to <br>
                as a result of an attacker. This attack is characterized by the result it <br>
                causes. As we will see, there are many ways to perform a DoS attack. A DoS <br>
                attack can cost an organization time and money while their services are <br>
                down. Depending on the importance of said services, the harm a DoS causes can vary.
            </p>
            <h4>What is a distributed denial-of-service (DDoS) attack?</h4>
            <p>
                The difference between a DoS and a DDoS attack is that in a DDoS attack, multiple <br>
                machines are being used to attack. In some DDoS attacks, hijacked machines can <br>
                be used to attack a target as well. Essentially, machines that were victims of <br>
                an attack can be used against a new victim.
            </p>

        @elseif ($step == 2)
            <h4>How does a denial-of-service attack work?</h4>
            <p>
                The most common way to perform a DoS attack is to find a way to overwhelm the target, <br>
                often times with junk traffic that wastes the server's time. As the target server <br>
                tries to process all of the traffic, it eventually becomes overwhelmed.<br>
                Another way to initiate a DoS attack is sending a server information that <br>
                causes it to crash, often times because of a programming oversight. <br>
            </p>

        @elseif ($step == 3)
            <h4>Examples of flooding attacks</h4>
            <ul>
                <li>Synflood: Overwhelm a server with connection requests until all ports are taken up</li>
                <li>Buffer Overflow: Send more traffic to a server than it was built to handle</li>
                <li>ICMP flood (Smurf): Send spoofed packets to multiple targets on a network</li>
            </ul>
        @elseif ($step == 4)
            <h4>Advantages of DDoS over DoS</h4>
            <p>
                Although a DDoS attack is still a DoS attack, a distributed attack has several advantages <br>
                over a basic denial-of-service attack:
            </p>
            <ul>
                <li>An attack can be more disruptive when carried out by multiple machines.</li>
                <li>The location of the attacker can be difficult to find, especially if machines in different locations are used.</li>
                <li>Shutting down many machines is harder than shutting down one.</li>
                <li>It is more difficult to attribute the source of the attack to one person (or team!).</li>
            </ul>
        @endif
        <!-- Reference:   -->
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
</div>
@endsection