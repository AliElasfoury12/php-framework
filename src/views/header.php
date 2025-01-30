<div 
    class = "flex justify-between items-center bg-blue absolute top-0 left-0 right-0 px-2 ">
    <h2 class="text-white"><a class="text-white" href="/">birdy</a></h2>
    <nav class="flex">
        @auth 
            <h3><a class="text-white mr-2" href="/profile"> {{$user->name}} </a></h3>
            <h3><a class="text-red mr-2" href="/logout">logout</a></h3>
        @endAuth

        @guest
            <h3><a class="text-white mr-2" href="/login">login</a></h3>
            <h3><a class="text-white" href="/register">register</a></h3>
        @endGuest
    </nav>
</div> 