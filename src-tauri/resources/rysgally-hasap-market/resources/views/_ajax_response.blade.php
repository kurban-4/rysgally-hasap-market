<!-- {{-- _ajax_response.blade.php --}}
<div id="productsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 40px;">
    @include('client.shop._product_list')
</div>

<div class="pagination-wrapper mt-5 pt-4 d-flex justify-content-center w-100" id="paginationLinks">
    {{ $products->links('pagination::bootstrap-5') }}
</div> -->