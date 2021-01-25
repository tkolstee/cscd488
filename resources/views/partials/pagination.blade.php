<div class="pagination">
    <a href={{$paginator->previousPageUrl()}}>Previous</a>
    <p>{{$paginator->currentPage()}}/{{$paginator->lastPage()}}</p>
    <a href={{$paginator->nextPageUrl()}}>Next</a>
</div>