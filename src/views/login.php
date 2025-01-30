<div class="pt-5">
    <h1 class="text-center">Login</h1>

    <form class="w-fit m-auto" method="post">
        <p> email</p> 
        <input class="input" type="text" name="email" placeholder="Email" value={{$user?->email}}> 
        <p class="text-red">{{ $errors?->email }}</p>

        <p> password</p>
        <input  class="input" type="password" name="password" placeholder="Password" value={{$user?->password}}> 
        <p class="text-red">{{ $errors?->password }}</p>

        <button type="submit">submit</button>
    </form>
</div>