@extends('redteam.base')

@section('title', 'Phishing Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/phishinglink">
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
            <h4>What is a phishing attack?</h4>
            <p>
                Phishing is a type of social engineering attack where an attacker in some <br>
                way impersonates a trusted entity. The victim is then tricked into giving <br>
                over important information such as credentials, or tricked into downloading <br>
                a malicious attachment. Phishing attacks are versatile in the sense that <br>
                there are many different ways phishing can be used.
            </p>

        @elseif ($step == 2)
            <h4>Ways of performing a phishing attack</h4>
            <p>There are 3 different phishing attacks possible in this game:</p>
            <ul>
                <li>Link: Getting a user to click on a link to a malicious website</li>
                <li>Credentials: Getting an employee to enter their credentials on a fake login screen</li>
                <li>Attachment: Getting an employee to open a malicious file attached to an email or other type of message</li>
            </ul>

        @elseif ($step == 3)
            <h4>How can a target prevent phishing attacks?</h4>
            <p>
                Users that are educated enough to spot the signs of a phishing attack are <br>
                less likely to fall prey to the attack. If the goal of a phishing attack <br>
                is to obtain user credentials, two-factor authentication can prevent the <br>
                credentials from being of much use. 
            </p>

        @elseif ($step == 4)
            <h4>Possible results of phishing attack</h4>
            <p>
                As mentioned earlier, phishing attacks can have a variety of results. If a user <br>
                downloads a malicious attachment, malware can be executed on the victim's machine. <br>
                This can include keyloggers, ransomware, etc. If a user's credentials are stolen, <br>
                the credentials can be used to get inside information about an organization. <br>
            </p>

        @endif
        <!-- Reference: https://www.imperva.com/learn/application-security/phishing-attack-scam/  -->
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