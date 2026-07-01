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