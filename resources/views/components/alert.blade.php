@if(session('success'))

<script>

document.addEventListener('DOMContentLoaded',()=>{

Swal.fire({

toast:true,

position:'top-end',

icon:'success',

title:'{{ session('success') }}',

showConfirmButton:false,

timer:3000

});

});

</script>

@endif
@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'warning',
        title: @json(session('error')),
        showConfirmButton: false,
        timer: 5000
    });
});
</script>
@endif
