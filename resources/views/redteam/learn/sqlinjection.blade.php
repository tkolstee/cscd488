@extends('redteam.base')

@section('title', 'SQL Injection Learning Page')

@section('pagecontent')
<div class="redLearn">
    <form method="POST" action="/learn/sqlinjection">
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
        <h4>What is SQL Injection?</h4>
        
            <p>SQL Injection is when the user enters input that manipulates the database queries called behind the scenes.</p>
            <p>For example, you go to view someones blog page and the url is</p>
            <p><strong>http://bestblog.com/posts?status=exists</strong></p>
            <p>Presumably you could change "exists" to something else and see the posts that have been deleted.</p>
            <p>Behind the scenes the query really looks like:</p>
            <p class="sqlInject"><strong>SELECT * FROM posts WHERE status = 'exists' OR (status != 'exists' AND admin = 1)</strong></p>
        @elseif ($step == 2)

       


        
            <h4>How to test for SQL Injection?</h4>
            <p><strong>http://bestblog.com/posts?status=exists</strong></p>
            <p>Since the query uses the literal string from the url, 
            <br>you can use things like a <strong>'</strong> to cause an error.</p>
            <p>If the url is now <strong>http://bestblog.com/posts?status=exists'</strong></p>
            <p>The resulting query will be <p class ="sqlInject"><strong>SELECT * FROM posts WHERE 
                status = 'exists'' OR (status != 'exists' AND admin = 1)</p></strong</p>
            <p>This will result in a SQL Error</p>
        @elseif ($step == 3)
       

        
            <h4>How to manipulate queries with SQL Injection?</h4>
            <p>Now that we know that the website is vulnerable to SQL Injection we can manipulate the query to access private data.</p>
            <p>If we want to access the posts with any status, even if you aren't admin, you can add</p>
            <p><strong>' OR 1=1--</strong></p>
            <p>The resulting query will be</p>
            <p class="sqlInject"><strong>SELECT * FROM posts WHERE status = 'exists' OR 1=1--' OR (status != 'exists' AND admin = 1)</strong></p>
            <p>First, the two dashed lines signal a comment in SQL so the query will effectively be</p>
            <p class="sqlInject"><strong>SELECT * FROM posts WHERE status = 'exists' OR 1=1</strong></p>
            <p>This query now has a WHERE clause that is always true and the website will now show posts of any status.</p>
        @elseif ($step == 4)
        


        
            <h4>Further Applications</h4>
            <p>You might not always need the where clause to be true all the time</p>
            <p>Sometimes it is sufficient enough just to get past the remaining requirements.</p>
            <p>For example if a URL <strong>http://government.gov/filename</strong> creates a query of</p>
            <p class="sqlInject"><strong>SELECT * FROM privatefiles WHERE name = 'filename' AND clearance = 5</strong></p>
            <p>You could get past the clearance checks by entering <strong>'--</strong> after "filename" in the URL.</p>
            <p>The query would be <p class ="sqlInject"><strong>SELECT * FROM privatefiles WHERE name = 'filename'--' AND clearance = 5</strong></p></p>
            <p>Commenting out the clearance check returning just the filename.</p>
        
        
    @endif
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
    </div><!-- end redSqlInjection-->
    
@endsection
