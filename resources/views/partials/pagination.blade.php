<div class="pagination">
    <a href={{$paginator->previousPageUrl()}}><button class="btn btn-pagination-prev">Previous</button></a>
    <p class="p-pagination">
        <select class="select-pagination" id="dynamic_select" onchange="if (this.value) window.location.href=this.value">
            @for($i = 0; $i < $paginator->lastPage(); $i++)
                <option value="{{$paginator->url($i+1)}}" <?php if($i + 1 == $paginator->currentPage()){ echo "selected"; }?>>{{ $i + 1 }}</option>
            @endfor
        </select>
        /{{$paginator->lastPage()}}
    </p>
    <a href={{$paginator->nextPageUrl()}}><button class="btn btn-pagination-next">Next</button></a>
</div>