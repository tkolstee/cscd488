<div class="pagination">
    <div class="pagination-left-container">
        @if($paginator->currentPage() > 1)
        <a href={{$paginator->previousPageUrl()}}><button class="btn btn-pagination-prev" formaction="{{$paginator->previousPageUrl()}}">Previous</button></a>
        @endif
        <div class="p-pagination">
            <select class="select-pagination" id="dynamic_select" onchange="if (this.value) window.location.href=this.value">
                @for($i = 0; $i < $paginator->lastPage(); $i++)
                    <option value="{{$paginator->url($i+1)}}" <?php if($i + 1 == $paginator->currentPage()){ echo "selected"; }?>>{{ $i + 1 }}</option>
                @endfor
            </select>
            /{{$paginator->lastPage()}}
</div>
    </div><!--end pagination-left-container-->
    <div class="pagination-right-container">
        @if($paginator->currentPage() < $paginator->lastPage())
            <a href={{$paginator->nextPageUrl()}}><button class="btn btn-pagination-next" formaction="{{$paginator->nextPageUrl()}}">Next</button></a>
        @endif
    </div><!--end pagination-right-container-->
</div>