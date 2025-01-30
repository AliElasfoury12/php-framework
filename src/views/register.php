<div class="pt-5">
    <h1 class="text-center">Register</h1>

    <form class="w-fit m-auto" method="post">
        <p>name</p>
        <input class="input" type="text" name="name" placeholder="User Name" value={{$user->name}} > 
        <p class="text-red">{{ $errors->name }}</p>

        <p> email</p> 
        <input class="input" type="text" name="email" placeholder="Email" value={{$user->email}}> 
        <p class="text-red">{{ $errors->email }}</p>

        <p> password</p>
        <input  class="input" type="password" name="password" placeholder="Password" value={{$user->password}}> 
        <p class="text-red">{{ $errors->password }}</p>

        <p>Confirm password</p>
        <input class="input" type="password" name="confirmPassword" placeholder="Password Confirm" value={{$user->confirmPassword}}>
        <p class="text-red">{{ $errors->confirmPassword }}</p>

        <button type="submit">submit</button>
    </form>
</div>