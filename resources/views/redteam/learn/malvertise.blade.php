@extends('redteam.base')

@section('title', 'Malvertise Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/malvertise">
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
        <h4>What is a Malvertising attack?</h4>
        <p>Malvertising is when an attacker injects malicious code somewhere <br>
        in a legitimate online advertising network. The attacker is then reaching <br>
        multiple websites using the advertising network (many of which are very <br>
        reputable).</p>



        <h4>How does an advertising network work?</h4>
        <p>The advertising system is very complex. The part of the system <br>
        that Malvertising exploits is that ads are obtained through <br>
        third-party services to deliver ads. In addition to that <br>
        even legitimate ads use multiple redirections before the landing page.</p>
    @elseif ($step == 2)



        <h4>How do attackers attack using an ad?</h4>
        <p>There are a lot of ways attackers can exploit the advertising <br>
        network, and some of these ways do not even require the user to click <br>
        on the ad. Some examples of this are: </p>

        <ul>
            <li>A Drive-by-Download: malware is installed when a user views the ads possible due to browser vulnerabilities.</li>
            <li>A Forced Redirect to a Malicious Website</li>
            <li>Displaying malicious content by executing javascript</li>
        </ul>

        <p>When users click on an ad it can cause: </p>

        <ul>
            <li>Execution of code that installs malware/adware on the user's computer</li>
            <li>A redirect to a malicious website instead of advertised website</li>
            <li>A redirect to a fake website that looks real (phishing attack)</li>
        </ul>



    @elseif ($step == 3)
        <h4>Other ways attackers can perform a Malvertising attack</h4>
        <p>As mentioned earlier, when even when a legitimate ad is clicked <br>
        on, there can be several redirects before the user reaches the <br>
        targeted landing page. An attacker can exploit this and compromise <br>
        one of these intermediate websites in order to attack anyone who <br>
        gets redirected there through a legitimate ad.</p>
    @elseif ($step == 4)



        <h4>Ways ad publishers can protect against Malvertising attacks</h4>
        <p>As an ad publisher, ways you can avoid hosting a malicious ad are:</p>

        <ul>
            <li>Make sure the ad networks, delivery paths,and security practices are good</li>
            <li>Scan the ad creative that is intended for the display to make sure it is safe</li>
            <li>Filter the file type of ads to prevent Javascript or other unwanted code</li>
        </ul>

    @endif
    <!-- Reference:  https://www.imperva.com/learn/application-security/malvertising/ -->
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
</div><!--end redMalvertise -->
@endsection
