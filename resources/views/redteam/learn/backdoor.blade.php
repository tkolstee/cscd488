@extends('redteam.base')

@section('title', 'Backdoor Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/backdoorbasic">
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
            <h4>What is a backdoor attack?</h4>
            <p>
                A backdoor attack uses malware to circumvent authentication procesures <br>
                to access a system. This can give an attacker access to resources that <br>
                the victim wouldn't want them to see. This attack is remote in nature, and <br>
                once the malware is installed, it can be difficult to detect a backdoor attack. <br>
                Mitigating this attack is also difficult as well.
            </p>

        @elseif ($step == 2)
            <h4>What is this attack used for?</h4>
            <p>
                Backdoor attacks can be used to steal data, deface websites, hijack servers, <br>
                and much more. Once a backdoor attack has succeeded against a victim, they may <br>
                also be weak to more types of attacks afterwards. For example, installing more malware <br>
                onto the host's machine. (This game uses 'Access Tokens' to indicate how much of a foothold) <br>
                an attacker has in their victim's system.
            </p>

        @elseif ($step == 3)
            <h4>Difficulties in mitigating backdoor attacks</h4>
            <p>
                Backdoor attacks can be difficult to detect. Malware can be hard to detect because <br>
                it's made to look like normal software. Some backdoor attacks can take advantage <br>
                of weaknesses in other software the victim is using. In some cases, even system <br>
                reinstallation will not truly mitigate a backdoor attack. One downside of this particular <br>
                attack is that failing it will always notify the victim of the attempt.
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