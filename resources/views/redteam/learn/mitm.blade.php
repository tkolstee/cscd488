@extends('redteam.base')

@section('title', 'Man in the Middle Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/mitm">
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
            <h4>What is a man-in-the-middle attack?</h4>
            <p>
                A man-in-the-middle attack is when a third party intercepts <br>
                communication between two parties.  This position can be leveraged <br>
                to alter data or steal credentials and personal information. A well <br>
                done man-in-the-middle attack can be difficult to detect.
            </p>

        @elseif ($step == 2)
            <h4>How does a man-in-the-middle attack work?</h4>
            <p>
                There are many ways an attacker can intercept traffic. One way is interfering<br>
                with traffic on a legitimate network. Another is setting up a fake network <br>
                that they control. Traffic is stripped of its encryption and may redirect <br>
                victims' traffic. The attacker may also re-encrypt the traffic and send it <br>
                to its intended destination. This is part of why a man-in-the-middle attack <br>
                can be difficult to notice.
            </p>

        @elseif ($step == 3)
            <h4>What happens after a man-in-the-middle attack?</h4>
            <p>
                In the real world, a man-in-the-middle attack doesn't always happen in <br>
                isolation. It can lead into other types of attacks. For example, network <br>
                traffic could be intercepted and a victim could be redirected to a phishing <br>
                website. Intercepting traffic on a banking website could allow someone to <br>
                steal money. Man-in-the-middle attacks can also be disruptive.
            </p>

        @elseif ($step == 4)
            <h4>How do you prevent a man-in-the-middle attack?</h4>
            <p>
                Using encryption protocols for your network can help prevent man-in-the-middle <br>
                attacks. It can be difficult, or impossible, to completely prevent traffic from <br>
                being intercepted. But encryption offers an extra layer of security. If <br>
                the attacker is unable to decrypt the traffic they intercepted, it won't be <br>
                of much use to them. Network users can also be educated on safe practices, <br>
                such as not using public Wi-Fi networks and using two-factor authentication. 
            </p>

        @endif
        <!-- Reference: https://www.csoonline.com/article/3340117/what-is-a-man-in-the-middle-attack-how-mitm-attacks-work-and-how-to-prevent-them.html -->
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