@extends('redteam.base')

@section('title', 'Supply Chain Attack Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/supplychainhw">
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
            <h4>What is a supply chain attack?</h4>
            <p>
                A supply chain attack is when an attacker exploits a weakness <br>
                in some third-party service the target is using. Often times, the <br>
                target of the attack is unaware of these vulnerabilities because they <br>
                did not create the service. Today, many organizations use third-party <br>
                software, hardware, or services, and these partners are trusted with <br>
                access to their systems and private data.
            </p>

        @elseif ($step == 2)
            <h4>Types of supply chain attacks</h4>
            <p>This game has 3 different types of supply chain attacks:</p>
            <ul>
                <li>Dev Tools: Compromise a tool or dependency used by the software development team</li>
                <li>Hardware: Exploit a weakness in the hardware a company bought from a third party</li>
                <li>Software: Exploit a weakness in the software a company bought from a third party</li>
            </ul>

        @elseif ($step == 3)
            <h4>How can a target prevent supply chain attacks?</h4>
            <p>
                Organizations can be more selective with which third-party services they use. For example, <br>
                in the healthcare sector, many places consider third-party risk and have regulations partners <br>
                must adhere to. Another possibility is to not use third-party services, but this can be difficult <br>
                to achieve in the real world. It is often times cheaper to not develop everything "in house". 
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