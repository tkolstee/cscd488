@extends('redteam.base')

@section('title', 'Drive-by Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/driveby">
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
            <h4>What is a Drive-By attack?</h4>
            <p>A Drive-By attack is when an attacker compromises a website, so when you visit it, you are forced<br>
                to download malware. Generally the type of malware forcibly downloaded is an exploit kit. Normally <br>
                designed to search for vulnerabilities in your computer's security, an exploit kit attacks and takes <br>
                control of your system. Generally these vulnerabilities will come from outdated applications such as <br>
                your operating system or browser.</p>
            <p>Attackers have the goal of infecting as many people as possible, so a Drive-By will generally target <br>
                a popular and reputable website. This makes it difficult to anticipate because you will likely think <br>
                the website is safe due to its reputation. This is, however, common and cannot be blamed on the website owner <br>
                as they are generally not aware of the existence of the Drive-By at all.</p>
        @elseif ($step == 2)
            <h4>Ways you might be Vulnerable:</h4>
            <p>After a Drive-By attack occurs and you download an exploit kit, it scans your system for vulnerabilities. <br>
                A huge factor in holding security vulnerabilities is outdated applications. Even internet browsers are <br>
                highly vulnerable. The top three internet browsers in 2016 had over 100 vulnerabilities each (Source: CVEdetails.com). <br>
                Another source of vulnerabilities is the plugins installed on your browser. A lot of people can let their <br>
                browser become filled with unsafe plugins while giving them permissions on your system. <br>
                Using a safe browser is also important while doing online banking or shopping so malware cannot <br>
                steal your banking or credit card information.</p>
            <p>Using technology can be very dangerous and way too many people don't understand that. Lots of people <br>
                will download free anti-virus software and think they are safe and sound on the internet. <br>
                The internet is extremely dangerous and no internet security has a guarantee of protecting <br>
                you from every attack out there let alone free security. Fileless malware can be installed, unbeknownst <br>
                to your internet security, on your RAM and does not use any files being hard to find. The technology <br>
                we use is way less safe than people generally think and this leads people to make vulnerable decisions.</p>
        @elseif ($step == 3)
            <h4>Ways to protect against Drive-By attacks:</h4>
            <p>A lot of vulnerability comes from having outdated applications, so in order to protect against that, <br>
                it is always a good idea to keep your applications updated to the newest security features. <br>
                Giving permission to plugins' developers is a possible vulnerability so making sure you only <br>
                install the plugins that are necessary, and only those from trusted sources is the best way to <br>
                ensure protection against plugins.</p>
            <p>Whenever using confidential information on the internet, use a separate, safe browser <br>
                in order to minimize the risk of the information being stolen. These come with some anti-virus <br>
                software, and this is one reason it is essential to have a reliable anti-virus software. <br>
                A built-in URL checker is a great feature from some anti-virus software and protects you <br>
                before you click on a link unlike misleading website-rating plugins. Some plugins are very <br>
                beneficial to your security such as Ad Blockers. These help stop adware by <br>
                preventing the malicious script from executing.</p>
        @endif
        <!-- Reference:  https://heimdalsecurity.com/blog/how-drive-by-download-attacks-work/ -->
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